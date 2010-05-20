from threequarters.blog.models import BlogItem
from threequarters.blog.search import BlogSearch
from pprint import pprint

s = BlogSearch()
s.create_index()
s.load_index()

items = BlogItem.objects.exclude(content_type__model='lastfmtrack')
print len(items)

for (i,blogitem) in enumerate(items):
        pprint ((i, blogitem.id,blogitem.content_object))
        s.add_blogitem(blogitem)
	#if (i%100==0):
	#	print "COMMITTING"
	#	s.commit()

s.commit()
print "ALL DONE"

