from django.conf.urls.defaults import *

urlpatterns = patterns('',
)


from django.views.generic.list_detail import object_list
def tag_wrapper(request, queryset, tag=None, *args, **kwargs):
    queryset = queryset.get(tag=tag).blogitem_set.all()
    kwargs["extra_context"] = {'tag': tag}
    return object_list(request, queryset, *args, **kwargs) 

from threequarters.blog.models import BlogItem, Tag, Twitter

blogitems_dict = {
    'queryset': BlogItem.objects.all(),
    'date_field': 'created_on',
    'extra_context': { 'twitters': Twitter.objects.all()[:3],
                     },
}

urlpatterns += patterns('django.views.generic.date_based',
   (r'^(?P<year>\d{4})/(?P<month>[a-z]{3})/(?P<day>\w{1,2})/(?P<slug>[-\w]+)/$', 'object_detail', dict(blogitems_dict, slug_field='slug')),
   (r'^(?P<year>\d{4})/(?P<month>[a-z]{3})/(?P<day>\w{1,2})/$',                  'archive_day',   blogitems_dict),
   (r'^(?P<year>\d{4})/(?P<month>[a-z]{3})/$',                                   'archive_month', blogitems_dict),
   (r'^(?P<year>\d{4})/week/(?P<week>\d{2})/$',                                  'archive_week', blogitems_dict),
   (r'^(?P<year>\d{4})/$',                                                       'archive_year',  blogitems_dict),
   (r'^/?$',                                                                     'archive_index', dict(blogitems_dict, num_latest=25, template_name="blog/index.html")),
)

# Tags
urlpatterns += patterns('threequarters.blog.urls',
    (r'^tag/(?P<tag>[-\w]+)/$', 'tag_wrapper', dict(queryset=Tag.objects.all(), paginate_by=20, template_name="blog/tag.html")),
)

# Feeds
urlpatterns += patterns('threequarters.blog.feeds',
    (r'^index.atom$', 'feed'),
    (r'^links/index.atom$', 'feed', {'linksonly': True}),
)

# Old MT redirects
urlpatterns += patterns('threequarters.blog.mtredirects',
    (r'^archives/(?P<year>\d{4})/(?P<month>\d{2})/(?P<day>\d{2})/(?P<slug>[-\w]+).html', 'entry'),
    (r'^links/archives/(?P<year>\d{4})/(?P<month>\d{2})/(?P<day>\d{2})-week/', 'linkarchive'),
)
