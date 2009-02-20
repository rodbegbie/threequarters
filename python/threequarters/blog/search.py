from django.conf import settings

from whoosh.fields import Schema, STORED, ID, KEYWORD, TEXT
from whoosh.qparser import QueryParser
import whoosh.index

import os
import os.path
import time

class BlogSearch(object):
    __shared_state = {}
    def __init__(self):
        self.__dict__ = self.__shared_state

    schema = Schema(title=TEXT(stored=True), body=TEXT, url=TEXT, via=TEXT, artist=TEXT,
                    id=ID(stored=True, unique=True), tags=KEYWORD(scorable=True), date=ID)
    index = None
    
    def create_index(self):
        if not os.path.exists(settings.WHOOSH_INDEX_DIR):
            os.mkdir(settings.WHOOSH_INDEX_DIR)
        
        self.index = whoosh.index.create_in(settings.WHOOSH_INDEX_DIR, self.schema)
    
    def load_index(self):
        self.index = whoosh.index.open_dir(settings.WHOOSH_INDEX_DIR)
        
    def search(self, term):
        if not self.index:
            self.load_index()

        searcher = self.index.searcher()
        parser = QueryParser("body", schema = self.schema)
        query = parser.parse(term)
        results = searcher.search(query, sortedby="date", reverse=True)
        return results
        
    def add_blogitem(self, item):
        if not self.index:
            self.load_index()
        
        fields = {}
        if item.content_type.model == 'post':
            fields['title'] = item.content_object.title
            fields['body'] = item.content_object.body_xhtml
            if item.content_object.tags:
                fields['tags'] = item.content_object.tags
        elif item.content_type.model == 'link':
            fields['title'] = item.content_object.title
            fields['body'] = item.content_object.description
            fields['url'] = item.content_object.url
            if item.content_object.tags:
                fields['tags'] = item.content_object.tags
            if item.content_object.via:
                fields['via'] = item.content_object.via
        elif self.content_type.model == 'flickrphoto':
            if not item.content_object.title:
                return # Don't bother indexing photos without titles
            fields['title'] = item.content_object.title
            fields['body'] = item.content_object.description
            fields['url'] = item.content_object.url
            if item.content_object.tags:
                fields['tags'] = item.content_object.tags
        elif self.content_type.model == 'amazoncd':
             fields['title'] = item.content_object.title
             fields['body'] = item.content_object.comments
             if item.content_object.artist:
                 fields['artist'] = item.content_object.artist
        elif self.content_type.model == 'twitter':
            fields['body'] = item.content_object.description
        elif self.content_type.model == 'lastfmtrack':
            return #Don't bother indexing LastFM tracks
    
        fields["id"] = item.id
        fields["date"] = time.mktime(item.content.created_on.timetuple())

        writer = self.index.writer()
        writer.update_document(**fields)
        writer.commit()