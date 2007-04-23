from threequarters.blog.models import *
from xapwrap.index import SmartIndex
from xapwrap.document import Document, TextField, SortKey

idx = SmartIndex('/var/www/threequarters/searchindex', True)

for blogitem in BlogItem.objects.all():
        print "INDEXING", blogitem.id
        if blogitem.content_type.model == 'post':
            textfields = [TextField('title', blogitem.content_object.title, True),
                          TextField('body', blogitem.content_object.body_xhtml, False)
                          ]
            if blogitem.content_object.tags:
                textfields.append(TextField('tags', blogitem.content_object.tags, True))
        elif blogitem.content_type.model == 'link':
            textfields = [TextField('title', blogitem.content_object.title, True),
                          TextField('body', blogitem.content_object.description, False),
                          TextField('url', blogitem.content_object.url, True),
                          ]
            if blogitem.content_object.tags:
                textfields.append(TextField('tags', blogitem.content_object.tags, True))
            if blogitem.content_object.via:
                textfields.append(TextField('via', blogitem.content_object.via, True))
        elif blogitem.content_type.model == 'flickrphoto':
            if not blogitem.content_object.title:
                continue #return # Don't bother indexing photos without titles
            textfields = [TextField('title', blogitem.content_object.title, True),
                          TextField('body', blogitem.content_object.description, False)
                          ]
            if blogitem.content_object.tags:
                textfields.append(TextField('tags', blogitem.content_object.tags, True))
        elif blogitem.content_type.model == 'amazoncd':
            textfields = [TextField('title', blogitem.content_object.title, True),
                          TextField('body', blogitem.content_object.comments, False)
                          ]
            if blogitem.content_object.artist:
                 textfields.append(TextField('artist', blogitem.content_object.artist, True))
        elif blogitem.content_type.model == 'twitter':
            textfields = [TextField('body', blogitem.content_object.description, False)
                          ]
        elif blogitem.content_type.model == 'lastfmtrack':
            continue #return #Don't bother indexing LastFM tracks



        doc = Document(textfields,
                   uid=blogitem.id+10000,
                   sortFields = [SortKey('date', blogitem.content_object.created_on)],
                   )
        idx.index(doc)

idx.close()
print "ALL DONE"

