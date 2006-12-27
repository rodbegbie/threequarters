from django.conf.urls.defaults import *

urlpatterns = patterns('',
#    (r'^$', 'django.views.generic.simple.direct_to_template', {'template': 'index.html'})#'threequarters.blog.views.homepage'),
)

from threequarters.blog.models import BlogItem

info_dict = {
    'queryset': BlogItem.objects.all(),
    'date_field': 'created_on',
}

urlpatterns = patterns('django.views.generic.date_based',
   (r'^(?P<year>\d{4})/(?P<month>[a-z]{3})/(?P<day>\w{1,2})/(?P<slug>[-\w]+)/$', 'object_detail', dict(info_dict, slug_field='slug')),
   (r'^(?P<year>\d{4})/(?P<month>[a-z]{3})/(?P<day>\w{1,2})/$',               'archive_day',   info_dict),
   (r'^(?P<year>\d{4})/(?P<month>[a-z]{3})/$',                                'archive_month', info_dict),
   (r'^(?P<year>\d{4})/$',                                                    'archive_year',  info_dict),
   (r'^/?$',                                                                  'archive_index', info_dict),
)
