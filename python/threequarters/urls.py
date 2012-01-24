from django.conf.urls.defaults import *
from django.contrib import admin

admin.autodiscover()

urlpatterns = patterns('',
     (r'^admin/', include(admin.site.urls)),
     (r'^comments/', include('django.contrib.comments.urls')),
     (r'', include('threequarters.blog.urls')),
)

from django.contrib.sitemaps import Sitemap
from threequarters.blog.models import Post

class JuiceSitemap(Sitemap):
    changefreq = "weekly"
    priority = 0.5

    def items(self):
        return Post.objects.filter(draft=False)

    def lastmod(self, obj):
        return obj.modified_on

sitemaps = {
    'juicers': JuiceSitemap,
}

urlpatterns += patterns('',
    (r'^sitemap\.xml$', 'django.contrib.sitemaps.views.sitemap', {'sitemaps': sitemaps}),
)
