from django.db import models
from django.contrib.contenttypes.models import ContentType

class Tag(models.Model):
    """A tag on an item."""
    tag = models.SlugField()
    display = models.CharField(maxlength=100)
    last_used = models.DateTimeField(default=models.LazyDate())
    
    class Meta:
        ordering = ["tag"]
    
    def __str__(self):
        return self.display

    def get_absolute_url(self):
        return "/tag/%s/" % (self.tag)

class Legacy(models.Model):
    mt_id = models.IntegerField(db_index=True, unique=True)
    basename = models.CharField(maxlength=50)
    
    class Admin:
        pass

class BlogItem(models.Model):
    legacy = models.ForeignKey(Legacy, null=True)
    tags = models.ManyToManyField(Tag)

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
    body_textile = models.TextField()
    body_xhtml = models.TextField(blank=True)
    tags = models.CharField(maxlength=255)
    draft = models.BooleanField(default=False)
    created_on = models.DateTimeField(default=models.LazyDate())
    modified_on = models.DateTimeField(default=models.LazyDate())

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
            tagitem = Tag.objects.get_or_create(tag=tag)[0]
            tagitem.display = display
            tagitem.save()
            blogitem.tags.add(tagitem)

    def get_absolute_url(self):
        return "/%s/%s/" % (self.created_on.strftime("%Y/%b/%d").lower(), self.slug)

    
class Link(models.Model):
    blogitem = models.GenericRelation(BlogItem)
    title = models.CharField(maxlength=255)
    slug = models.SlugField(maxlength=30, prepopulate_from=("title",))
    description = models.TextField()
    url = models.URLField()
    via = models.URLField(blank=True)
    tags = models.CharField(maxlength=255)
    created_on = models.DateTimeField(default=models.LazyDate())
    modified_on = models.DateTimeField(default=models.LazyDate())

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
            tagitem = Tag.objects.get_or_create(tag=tag)[0]
            tagitem.display = display
            tagitem.save()
            blogitem.tags.add(tagitem)

class FlickrPhoto(models.Model):
    blogitem = models.GenericRelation(BlogItem)
    flickr_id = models.IntegerField(db_index=True)
    title = models.CharField(maxlength=255)
    description = models.TextField()
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
            tagitem = Tag.objects.get_or_create(tag=tag)[0]
            tagitem.display = display
            tagitem.save()
            blogitem.tags.add(tagitem)

(AMAZON_COM,
 AMAZON_CO_UK) = range(2)
AMAZON_COUNTRIES = ("us", "uk")
AMAZON_CHOICES=[(n, AMAZON_COUNTRIES[n]) for n in range(2)]
AMAZON_URLS=("http://amazon.com/o/ASIN/%s/groovymother-20/ref=nosim/",
             "http://amazon.co.uk/o/ASIN/%s/groovymother-21/ref=nosim/",
             )

class AmazonCD(models.Model):
    blogitem = models.GenericRelation(BlogItem)
    asin = models.CharField(db_index=True, maxlength=15)
    store = models.IntegerField(choices=AMAZON_CHOICES, default=AMAZON_COM)
    title = models.CharField(maxlength=255, blank=True)
    artist = models.CharField(maxlength=255, blank=True)
    image_url = models.URLField(blank=True)
    comments = models.TextField(blank=True)
    created_on = models.DateTimeField(default=models.LazyDate())

    class Admin:
        list_display = ('asin', 'store', 'title', 'created_on')
        list_filter = ['store', 'created_on']

    class Meta:
        ordering = ["-created_on"]
    
    def get_absolute_url(self):
        return AMAZON_URLS[self.store] % self.asin

    def save(self):
        if not self.title:
            from threequarters import amazon
            amazon.setLicense("1AGTVVHBTYPBQKT7G482")
            res = amazon.searchByASIN(self.asin, locale=AMAZON_COUNTRIES[self.store])[0]
            print res.ProductName.encode('UTF-8')
            self.title = res.ProductName
            try:
                artist = res.Artists.Artist
                if isinstance(artist, list):
                    artist = artist[0]
                self.artist = artist
            except:
                # artist name failed for some reason
                # Audiobook?
                pass
            self.image_url = res.ImageUrlSmall

        super(AmazonCD, self).save() # Call the "real" save() method.

        if not self.blogitem.all():
            blogitem = self.blogitem.create(created_on=self.created_on,
                                            slug=self.asin)
        else:
            blogitem = self.blogitem.get()
            blogitem.created_on = self.created_on
            blogitem.slug = self.asin
            blogitem.save()



class Twitter(models.Model):
    twitter_id = models.IntegerField(db_index=True)
    description = models.TextField()
    created_on = models.DateTimeField(default=models.LazyDate())

    class Admin:
        list_display = ('description', 'created_on')
        list_filter = ['created_on']

    class Meta:
        ordering = ["-created_on"]
    
    def get_absolute_url(self):
        return "http://twitter.com/rodbegbie/statuses/%d" % self.twitter_id


