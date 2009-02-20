from django.conf.urls.defaults import *
from django.contrib import admin

admin.autodiscover()

urlpatterns = patterns('',
     (r'^admin/(.*)', admin.site.root),
     (r'^comments/', include('django.contrib.comments.urls')),
     (r'', include('threequarters.blog.urls')),
)
