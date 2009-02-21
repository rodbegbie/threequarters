from threequarters.blog.models import BlogItem
from threequarters.blog.search import BlogSearch
from pprint import pprint

s = BlogSearch()
s.create_index()

for blogitem in BlogItem.objects.exclude(content_type__model='lastfmtrack'):
        pprint (blogitem.content_object)
        s.add_blogitem(blogitem)
print "ALL DONE"

