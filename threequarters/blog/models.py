from django.db import models
from django.contrib.contenttypes.models import ContentType

class TaggedItem(models.Model):
    """A tag on an item."""
    tag = models.SlugField()
    display = models.CharField(maxlength=100)
    content_type = models.ForeignKey(ContentType)
    object_id = models.PositiveIntegerField()
    
    content_object = models.GenericForeignKey()
    
    class Meta:
        ordering = ["tag"]
    
    def __str__(self):
        return self.display

class Legacy(models.Model):
    content_type = models.ForeignKey(ContentType)
    object_id = models.PositiveIntegerField()
    content_object = models.GenericForeignKey()
    mt_id = models.IntegerField(db_index=True, unique=True)
    basename = models.CharField(maxlength=50)
    
    class Admin:
        pass

class Post(models.Model):
    title = models.CharField(maxlength=255)
    slug = models.SlugField(maxlength=30, prepopulate_from=("title",))
    body_textile = models.TextField()
    body_xhtml = models.TextField()
    created_on = models.DateTimeField(default=models.LazyDate())
    modified_on = models.DateTimeField(default=models.LazyDate())
    legacy = models.GenericRelation(Legacy)
    draft = models.BooleanField(default=False)
    tags = models.GenericRelation(TaggedItem)

    class Admin:
        list_display = ('title', 'slug', 'created_on', 'draft')
    list_filter = ['created_on', 'draft']

    class Meta:
        ordering = ["-created_on"]

    def save(self):
        import textile 
        self.body_xhtml = textile.textile(self.body_textile)
        super(Post, self).save() # Call the "real" save() method.

    def get_absolute_url(self):
        return "/weblog/%s/%s/" % (self.created_on.strftime("%Y/%b/%d").lower(), self.slug)

    
class Link(models.Model):
    title = models.CharField(maxlength=255)
    slug = models.SlugField(maxlength=30, prepopulate_from=("title",))
    description = models.TextField()
    url = models.URLField()
    via = models.URLField(blank=True)
    created_on = models.DateTimeField(default=models.LazyDate())
    modified_on = models.DateTimeField(default=models.LazyDate())
    legacy = models.GenericRelation(Legacy)
    tags = models.GenericRelation(TaggedItem)

    class Admin:
        list_display = ('title', 'url', 'created_on')
        list_filter = ['created_on']

    class Meta:
        ordering = ["-created_on"]
    
    def get_absolute_url(self):
        return "/weblog/%s/%s/" % (self.created_on.strftime("%Y/%b/%d").lower(), self.slug)

