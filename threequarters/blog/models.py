from django.db import models
from django.contrib.contenttypes.models import ContentType

class TaggedItem(models.Model):
    """A tag on an item."""
    tag = models.SlugField()
    display = models.CharField(maxlength=100)
    last_used = models.DateTimeField(default=models.LazyDate())
    
    class Meta:
        ordering = ["tag"]
    
    def __str__(self):
        return self.display

    def get_absolute_url(self):
        return "/links/tag/%s/" % (self.tag)

class Legacy(models.Model):
    mt_id = models.IntegerField(db_index=True, unique=True)
    basename = models.CharField(maxlength=50)
    
    class Admin:
        pass

class BlogItem(models.Model):
    legacy = models.ForeignKey(Legacy, null=True)
    tags = models.ManyToManyField(TaggedItem)

    created_on = models.DateTimeField(default=models.LazyDate())
    slug = models.SlugField()

    content_type = models.ForeignKey(ContentType)
    object_id = models.PositiveIntegerField()
    content_object = models.GenericForeignKey()

    class Meta:
        ordering = ["-created_on"]

class Post(models.Model):
    blogitem = models.GenericRelation(BlogItem)
    title = models.CharField(maxlength=255)
    slug = models.SlugField(maxlength=30, prepopulate_from=("title",))
    created_on = models.DateTimeField(default=models.LazyDate())
    modified_on = models.DateTimeField(default=models.LazyDate())
    body_textile = models.TextField()
    body_xhtml = models.TextField()
    draft = models.BooleanField(default=False)

    class Admin:
        list_display = ('title', 'slug', 'created_on', 'draft')
        list_filter = ['created_on', 'draft']

    class Meta:
        ordering = ["-created_on"]

    def save(self):
        import textile 
        self.body_xhtml = textile.textile(self.body_textile)
        super(Post, self).save() # Call the "real" save() method.

        if not self.blogitem.all():
            self.blogitem.create(created_on=self.created_on,
                                 slug=self.slug)
        else:
            blogitem = self.blogitem.get()
            blogitem.created_on = self.created_on
            blogitem.slug = self.slug
            blogitem.save()

    def get_absolute_url(self):
        return "/%s/%s/" % (self.created_on.strftime("%Y/%b/%d").lower(), self.slug)

    
class Link(models.Model):
    blogitem = models.GenericRelation(BlogItem)
    title = models.CharField(maxlength=255)
    slug = models.SlugField(maxlength=30, prepopulate_from=("title",))
    created_on = models.DateTimeField(default=models.LazyDate())
    modified_on = models.DateTimeField(default=models.LazyDate())
    description = models.TextField()
    url = models.URLField()
    via = models.URLField(blank=True)
    tags = models.CharField(maxlength=255)

    class Admin:
        list_display = ('title', 'url', 'created_on')
        list_filter = ['created_on']

    class Meta:
        ordering = ["-created_on"]
    
    def get_absolute_url(self):
        return "/%s/#%s" % (self.created_on.strftime("%Y/%b/%d").lower(), self.slug)

    def save(self):
        super(Link, self).save() # Call the "real" save() method.

        if not self.blogitem.all():
            blogitem = self.blogitem.create(created_on=self.created_on,
                                            slug=self.slug)
        else:
            blogitem = self.blogitem.get()
            blogitem.created_on = self.created_on
            blogitem.slug = self.slug
            blogitem.save()

        blogitem.tags.clear()
        import string
        from threequarters.utils import Translator
        trans = Translator(keep=string.digits+string.lowercase)
        for display in self.tags.split(","):
            display = display.strip()
            tag = trans(display.lower())
            tagitem = TaggedItem.objects.get_or_create(tag=tag)[0]
            tagitem.display = display
            tagitem.save()
            blogitem.tags.add(tagitem)

class FlickrPhoto(models.Model):
    blogitem = models.GenericRelation(BlogItem)
    flickr_id = models.IntegerField(db_index=True)
    title = models.CharField(maxlength=255)
    description_original = models.TextField()
    description_xhtml = models.TextField()
    flickr_url = models.URLField()
    image_url = models.URLField()
    image_width = models.IntegerField(default=0)
    image_height = models.IntegerField(default=0)
    tags = models.CharField(maxlength=255)
    created_on = models.DateTimeField(default=models.LazyDate())

    class Admin:
        list_display = ('title', 'created_on')
        list_filter = ['created_on']

    class Meta:
        ordering = ["-created_on"]
    
    def get_absolute_url(self):
        return self.flickr_url

    def save(self):
        import textile 
        self.description_xhtml = textile.textile(self.description_original)
        super(FlickrPhoto, self).save() # Call the "real" save() method.

        if not self.blogitem.all():
            blogitem = self.blogitem.create(created_on=self.created_on,
                                            slug="")
        else:
            blogitem = self.blogitem.get()
            blogitem.created_on = self.created_on
            blogitem.slug = ""
            blogitem.save()

        blogitem.tags.clear()
        import string
        from threequarters.utils import Translator
        trans = Translator(keep=string.digits+string.lowercase)
        for display in self.tags.split(","):
            display = display.strip()
            tag = trans(display.lower())
            tagitem = TaggedItem.objects.get_or_create(tag=tag)[0]
            tagitem.display = display
            tagitem.save()
            blogitem.tags.add(tagitem)

