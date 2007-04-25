from django.conf.urls.defaults import *

urlpatterns = patterns('',
     (r'^admin/', include('django.contrib.admin.urls')),
     (r'^comments/', include('threequarters.comments.urls.comments')),
     (r'^openid/$', 'threequarters.django_openidconsumer.views.begin'),
     (r'^openid/complete/$', 'threequarters.django_openidconsumer.views.complete'),
     (r'^openid/signout/$', 'threequarters.django_openidconsumer.views.signout'),
     (r'', include('threequarters.blog.urls')),
)
