from threequarters.blog.models import BlogItem
from threequarters.blog.search import BlogSearch
from pprint import pprint

s = BlogSearch()

for blogitem in BlogItem.objects.exclude(content_type__model='lastfmtrack'):
        pprint (blogitem)
        s.add_blogitem(blogitem)
print "ALL DONE"

