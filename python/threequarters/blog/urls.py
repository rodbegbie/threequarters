from django.conf.urls.defaults import *
from threequarters.blog.models import BlogItem, Tag, Twitter, LastFMTrack, Location
from threequarters.blog.search import BlogSearch
urlpatterns = patterns('',
)


from django.views.generic.list_detail import object_list
def tag_wrapper(request, queryset, tag=None, *args, **kwargs):
    try:
        queryset = queryset.get(tag=tag).blogitem_set.all()
    except:
        # Tag not found
        from django.http import Http404
        raise Http404
    kwargs["extra_context"] = {'tag': tag, 'location': Location.objects.all()[:1] }
    return object_list(request, queryset, *args, **kwargs)

def search_wrapper(request, queryset, *args, **kwargs):
    q = request.GET.get("q", "")
    results = BlogSearch().search(q)
    if not results:
        # No responses.  pass on empty queryset
        queryset = queryset.filter(id=0)
    else:
        queryset = queryset.filter(id__in=[int(result["id"]) for result in results])
    kwargs["extra_context"] = {'q': q, 'location': Location.objects.all()[:1] }
    return object_list(request, queryset, *args, **kwargs)

blogitems_dict = {
#'queryset': BlogItem.objects.all().exclude(content_type__model="lastfmtrack").exclude(content_type__model="twitter", content_object__description__startswith="@"),
    'queryset': BlogItem.objects.all().filter(display_on_homepage = True),
    'date_field': 'created_on',
    'extra_context': { 'lastfmtracks': LastFMTrack.objects.all()[:3], 'location': Location.objects.all()[:1] },
}

urlpatterns += patterns('django.views.generic.date_based',
   (r'^(?P<year>\d{4})/(?P<month>[a-z]{3})/(?P<day>\w{1,2})/(?P<slug>[-\w]+)/$', 'object_detail', dict(blogitems_dict, slug_field='slug')),
   (r'^(?P<year>\d{4})/(?P<month>[a-z]{3})/(?P<day>\w{1,2})/$',                  'archive_day',   blogitems_dict),
   (r'^(?P<year>\d{4})/(?P<month>[a-z]{3})/$',                                   'archive_month', blogitems_dict),
   (r'^(?P<year>\d{4})/week/(?P<week>\d{1,2})/$',                                  'archive_week', dict(blogitems_dict, allow_empty=False)),
#   (r'^(?P<year>\d{4})/$',                                                       'archive_year',  blogitems_dict),
   (r'^/?$',                                                                     'archive_index', dict(blogitems_dict, num_latest=50, template_name="blog/index.html")),
)

# Tags
urlpatterns += patterns('threequarters.blog.urls',
    (r'^tag/(?P<tag>[-\w]+)/$', 'tag_wrapper', dict(queryset=Tag.objects.all(), paginate_by=500, template_name="blog/tag.html", allow_empty=False)),
)

# Search
urlpatterns += patterns('threequarters.blog.urls',
    (r'^search/$', 'search_wrapper', dict(queryset=BlogItem.objects.all(), paginate_by=20, template_name="blog/search.html", allow_empty=True)),
)

# Feeds
urlpatterns += patterns('threequarters.blog.feeds',
    (r'^index.atom$', 'feed'),
    (r'^links/index.atom$', 'feed', {'linksonly': True}),
)

from threequarters.blog.feeds import CommentsFeed

urlpatterns += patterns('',
	(r'^feeds/comments/$', CommentsFeed()),
)

# Old MT redirects
urlpatterns += patterns('threequarters.blog.mtredirects',
    (r'^archives/(?P<year>\d{4})/(?P<month>\d{2})/(?P<day>\d{2})/(?P<slug>[-\w]+)\.html', 'entry'),
    (r'^archives/(?P<year>\d{4})/(?P<month>\d{2})/', 'month'),
    (r'^archives/(?P<year>\d{4})_(?P<month>\d{2}).html', 'month'),
    (r'^archives/(?P<year>\d{4})/(?P<month>\d{2})/(?P<day>\d{2})/(?P<slug>[-\w]+)\.html', 'entry'),
    (r'^archives/(?P<id>\d{6})\.html', 'entryById'),
    (r'^links/archives/(?P<year>\d{4})/(?P<month>\d{2})/(?P<day>\d{2})-week/', 'linkarchive'),
    (r'^archives/week_(?P<year>\d{4})_(?P<month>\d{2})_(?P<day>\d{2}).html', 'linkarchive'),
    (r'^links/archives/week_(?P<year>\d{4})_(?P<month>\d{2})_(?P<day>\d{2}).html', 'linkarchive'),
    (r'^links/tag/(?P<tag>[-\w]+)/', 'linktag'),
)

# Static redirects
urlpatterns += patterns('django.views.generic.simple',
    (r'^images/(?P<image>.*)$', 'redirect_to', {'url': 'http://static.groovymother.com/images/%(image)s'}),
    (r'^photos/(?P<image>.*)$', 'redirect_to', {'url': 'http://static.groovymother.com/photos/%(image)s'}),
    (r'^downloads/(?P<image>.*)$', 'redirect_to', {'url': 'http://static.groovymother.com/downloads/%(image)s'}),
    (r'^mirror/(?P<image>.*)$', 'redirect_to', {'url': 'http://static.groovymother.com/mirror/%(image)s'}),
)

