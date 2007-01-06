from django.conf.urls.defaults import *

urlpatterns = patterns('',
     (r'^admin/', include('django.contrib.admin.urls')),
     (r'^comments/', include('threequarters.comments.urls.comments')),
     (r'', include('threequarters.blog.urls')),
)
