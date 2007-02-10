from django.db import models
from django.contrib.contenttypes.models import ContentType
from datetime import datetime

def blogitem_save(object, slug="", tags=""):
    if not object.blogitem.all():
        blogitem = object.blogitem.create(created_on=object.created_on,
                                          slug=slug)
    else:
        blogitem = object.blogitem.get()
        blogitem.created_on = object.created_on
        blogitem.slug = slug
        blogitem.save()
   
    if tags:
        blogitem.tags.clear()
        import string
        from threequarters.utils import Translator
        trans = Translator(keep=string.digits+string.lowercase)
        for display in tags.split(","):
            display = display.strip()
            tag = trans(display.lower())
            (tagitem, created) = Tag.objects.get_or_create(tag=tag)
            tagitem.display = display
            tagitem.save()
            blogitem.tags.add(tagitem)

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
    slug = models.SlugField(blank=True)

    content_type = models.ForeignKey(ContentType)
    object_id = models.PositiveIntegerField()
    content_object = models.GenericForeignKey()

    def __str__(self):
        return self.slug

    class Meta:
        ordering = ["-created_on"]

    def save(self):
        super(BlogItem, self).save() # Call the "real" save() method.

        # Save to search index
        from xapwrap.index import SmartIndex
        from xapwrap.document import Document, TextField, SortKey
        idx = SmartIndex('/var/www/threequarters/searchindex', False)

        if self.content_type.model == 'post':
            textfields = [TextField('title', self.content_object.title, True),
                          TextField('body', self.content_object.body_xhtml, False)
                          ]
            if self.content_object.tags:
                textfields.append(TextField('tags', self.content_object.tags, True))
        elif self.content_type.model == 'link':
            textfields = [TextField('title', self.content_object.title, True),
                          TextField('body', self.content_object.description, False),
                          TextField('url', self.content_object.url, True),
                          ]
            if self.content_object.tags:
                textfields.append(TextField('tags', self.content_object.tags, True))
            if self.content_object.via:
                textfields.append(TextField('via', self.content_object.via, True))
        elif self.content_type.model == 'flickrphoto':
            if not self.content_object.title:
                return # Don't bother indexing photos without titles
            textfields = [TextField('title', self.content_object.title, True),
                          TextField('body', self.content_object.description, False)
                          ]
            if self.content_object.tags:
                textfields.append(TextField('tags', self.content_object.tags, True))
        elif self.content_type.model == 'amazoncd':
            textfields = [TextField('title', self.content_object.title, True),
                          TextField('body', self.content_object.comments, False)
                          ]
            if self.content_object.artist:
                 textfields.append(TextField('artist', self.content_object.artist, True))
        elif self.content_type.model == 'twitter':
            textfields = [TextField('body', self.content_object.description, False)
                          ]
        elif self.content_type.model == 'lastfmtrack':
            return #Don't bother indexing LastFM tracks

        doc = Document(textfields,
                       uid=self.id+10000,
                       sortFields = [SortKey('date', self.content_object.created_on)],
                       )
        idx.index(doc)
        idx.close()



class Post(models.Model):
    blogitem = models.GenericRelation(BlogItem)
    title = models.CharField(maxlength=255)
    slug = models.SlugField(maxlength=30, prepopulate_from=("title",))
    body_textile = models.TextField()
    body_xhtml = models.TextField(blank=True)
    tags = models.CharField(maxlength=255, blank=True)
    draft = models.BooleanField(default=False)
    created_on = models.DateTimeField(default=models.LazyDate())
    modified_on = models.DateTimeField(default=models.LazyDate())

    class Admin:
        list_display = ('title', 'slug', 'created_on', 'draft')
        list_filter = ['created_on', 'draft']

    class Meta:
        ordering = ["-created_on"]

    def save(self):
        self.modified_on = datetime.now()
        import textile 
        self.body_xhtml = textile.textile(self.body_textile)
        super(Post, self).save() # Call the "real" save() method.

        blogitem_save(self, self.slug, self.tags)


    def get_absolute_url(self):
        return "/%s/%s/" % (self.created_on.strftime("%Y/%b/%d").lower(), self.slug)

    def __str__(self):
        return self.slug

    
class Link(models.Model):
    blogitem = models.GenericRelation(BlogItem)
    title = models.CharField(maxlength=255)
    slug = models.SlugField(maxlength=30, prepopulate_from=("title",))
    description = models.TextField()
    url = models.URLField()
    #verify_exists=False)
    via = models.URLField(blank=True)
    tags = models.CharField(maxlength=255)
    created_on = models.DateTimeField(default=models.LazyDate())
    modified_on = models.DateTimeField(default=models.LazyDate())

    class Admin:
        list_display = ('title', 'created_on')
        list_filter = ['created_on']

    class Meta:
        ordering = ["-created_on"]
    
    def get_absolute_url(self):
        from datetime import timedelta
        sunday = self.created_on - timedelta(days=int(self.created_on.strftime("%w")))
        year = int(sunday.strftime("%Y"))
        week = int(sunday.strftime("%W"))+1
        
        # Django's weekly view starts on Sunday, while strftime starts on Monday
        # So add one week for Sundays.
        #if self.created_on.strftime("%a") == "Sun":
        #    week = week - 1
        
        return "/%d/week/%d/#%s" % (year, week, self.slug)

    def __str__(self):
        return self.slug

    def save(self):
        self.modified_on = datetime.now()
        super(Link, self).save() # Call the "real" save() method.
        blogitem_save(self, self.slug, self.tags)


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

    def __str__(self):
        return self.title

    def save(self):
        super(FlickrPhoto, self).save() # Call the "real" save() method.
        blogitem_save(self, tags=self.tags)


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

    def __str__(self):
        return self.title

    def save(self):
        if not self.title:
            from threequarters import amazon
            amazon.setLicense("1AGTVVHBTYPBQKT7G482")
            res = amazon.searchByASIN(self.asin, locale=AMAZON_COUNTRIES[int(self.store)])[0]
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

        blogitem_save(self)


class Twitter(models.Model):
    blogitem = models.GenericRelation(BlogItem)
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

    def __str__(self):
        return self.description

    def save(self):
        super(Twitter, self).save() # Call the "real" save() method.
        blogitem_save(self)

class LastFMTrack(models.Model):
    blogitem = models.GenericRelation(BlogItem)
    last_fm_id = models.IntegerField()
    artist = models.CharField(maxlength=255)
    title = models.CharField(maxlength=255)
    last_fm_url = models.URLField()
    created_on = models.DateTimeField(default=models.LazyDate())

    class Admin:
        list_display = ('artist','title', 'created_on')
        list_filter = ['created_on']

    class Meta:
        ordering = ["-created_on"]
    
    def get_absolute_url(self):
        return self.last_fm_url

    def __str__(self):
        return self.title

    def save(self):
        super(LastFMTrack, self).save() # Call the "real" save() method.
        blogitem_save(self)

class YelpReview(models.Model):
    blogitem = models.GenericRelation(BlogItem)
    business = models.CharField(maxlength=255)
    review = models.TextField()
    score = models.IntegerField()
    yelp_url = models.URLField()
    created_on = models.DateTimeField(default=models.LazyDate())

    class Admin:
        list_display = ('business', 'created_on')
        list_filter = ['created_on']

    class Meta:
        ordering = ["-created_on"]
    
    def get_absolute_url(self):
        return self.yelp_url

    def __str__(self):
        return self.business

    def save(self):
        super(YelpReview, self).save() # Call the "real" save() method.
        blogitem_save(self)
