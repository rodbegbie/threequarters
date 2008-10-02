from django.contrib import admin
from models import *

class PostAdmin(admin.ModelAdmin):
    list_display = ('title', 'slug', 'created_on', 'draft')
    list_filter = ['created_on', 'draft']
    prepopulated_fields = {"slug": ("title",)}
admin.site.register(Post, PostAdmin)

class LinkAdmin(admin.ModelAdmin):
    list_display = ('title', 'created_on')
    list_filter = ['created_on']
    prepopulated_fields = {"slug": ("title",)}
admin.site.register(Link, LinkAdmin)

class LocationAdmin(admin.ModelAdmin):
    list_display = ["name", "located_at"]
admin.site.register(Location, LocationAdmin)


class AmazonCDAdmin(admin.ModelAdmin):
    list_display = ["asin", "artist", "title", "created_on"]
admin.site.register(AmazonCD, AmazonCDAdmin)
