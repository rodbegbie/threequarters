from django.db import models
from django.contrib.contenttypes import generic
from django.contrib.contenttypes.models import ContentType
from threequarters.blog.fields import BigIntegerField
from threequarters.blog.search import BlogSearch
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

class Location(models.Model):
    """A location from Fire Eagle"""
    name = models.CharField(max_length=255)
    located_at = models.DateTimeField()

    class Meta:
        ordering = ["-located_at"]

    def __str__(self):
        return self.name

class Tag(models.Model):
    """A tag on an item."""
    tag = models.SlugField()
    display = models.CharField(max_length=100)
    last_used = models.DateTimeField(auto_now=True)
    
    class Meta:
        ordering = ["tag"]
    
    def __str__(self):
        return self.display

    def get_absolute_url(self):
        return "/tag/%s/" % (self.tag)

class Legacy(models.Model):
    mt_id = models.IntegerField(db_index=True, unique=True)
    basename = models.CharField(max_length=50)
    
class BlogItem(models.Model):
    legacy = models.ForeignKey(Legacy, null=True)
    tags = models.ManyToManyField(Tag)

    created_on = models.DateTimeField(default=datetime.now)
    slug = models.SlugField(blank=True)

    content_type = models.ForeignKey(ContentType)
    object_id = models.PositiveIntegerField()
    content_object = generic.GenericForeignKey()
    
    display_on_homepage = models.BooleanField(default=True)

    def __str__(self):
        return self.slug

    class Meta:
        ordering = ["-created_on"]

    def age(self):
        from datetime import datetime
        delta = datetime.today() - self.created_on
        return delta.days

    def get_absolute_url(self):
        return self.content_object.get_absolute_url()

    def save(self, *args, **kwargs):
        # Check if it's to be displayed on the homepage
        if ((self.content_type.model == 'post' and self.content_object.draft) or
            (self.content_type.model == 'twitter' and self.content_object.starts_with_at) or
             self.content_type.model == 'lastfmtrack'):
            self.display_on_homepage = False
        else:
            self.display_on_homepage = True

        super(BlogItem, self).save(*args, **kwargs) # Call the "real" save() method.
        
        BlogSearch().add_blogitem(self)


class Post(models.Model):
    blogitem = generic.GenericRelation(BlogItem)
    title = models.CharField(max_length=255)
    slug = models.SlugField(max_length=30)
    body_textile = models.TextField()
    body_xhtml = models.TextField(blank=True)
    tags = models.CharField(max_length=255, blank=True)
    draft = models.BooleanField(default=False)
    created_on = models.DateTimeField(default=datetime.now)
    modified_on = models.DateTimeField(default=datetime.now)

    class Meta:
        ordering = ["-created_on"]

    def save(self, *args, **kwargs):
        import textile 
        self.modified_on = default=datetime.now()
        self.body_xhtml = textile.textile(self.body_textile.encode('utf-8'),
                        encoding='utf-8',
                        output='utf-8')
        super(Post, self).save(*args, **kwargs) # Call the "real" save() method.

        blogitem_save(self, self.slug, self.tags)


    def get_absolute_url(self):
        return "/%s/%s/" % (self.created_on.strftime("%Y/%b/%d").lower(), self.slug)

    def __str__(self):
        return self.slug

    
class Link(models.Model):
    blogitem = generic.GenericRelation(BlogItem)
    title = models.CharField(max_length=255)
    slug = models.SlugField(max_length=30)
    description = models.TextField()
    url = models.URLField(verify_exists=False)
    #models.CharField(max_length=200) #models.URLField(verify_exists=False)
    via = models.URLField(blank=True)
    tags = models.CharField(max_length=255)
    has_thumbnail = models.BooleanField(default=False)
    generate_thumbnail = models.BooleanField(default=True)
    created_on = models.DateTimeField(default=datetime.now)
    modified_on = models.DateTimeField(default=datetime.now)

    class Meta:
        ordering = ["-created_on"]

    def get_comments_url(self):
        return "/%s/%s/#comments" % (self.created_on.strftime("%Y/%b/%d").lower(), self.slug)
    
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

    def save(self, *args, **kwargs):
        self.modified_on = datetime.now()
        super(Link, self).save(*args, **kwargs) # Call the "real" save() method.
        blogitem_save(self, self.slug, self.tags)


class FlickrPhoto(models.Model):
    blogitem = generic.GenericRelation(BlogItem)
    flickr_id = BigIntegerField(db_index=True)
    title = models.CharField(max_length=255)
    description = models.TextField()
    flickr_url = models.URLField()
    image_url = models.URLField()
    image_width = models.IntegerField(default=0)
    image_height = models.IntegerField(default=0)
    tags = models.CharField(max_length=255)
    created_on = models.DateTimeField(default=datetime.now)

    class Meta:
        ordering = ["-created_on"]
    
    def get_absolute_url(self):
        return self.flickr_url

    def __str__(self):
        return self.title

    def save(self, *args, **kwargs):
        super(FlickrPhoto, self).save(*args, **kwargs) # Call the "real" save() method.
        blogitem_save(self, tags=self.tags)


class VimeoClip(models.Model):
    blogitem = generic.GenericRelation(BlogItem)
    vimeo_id = models.IntegerField(db_index=True)
    title = models.CharField(max_length=255)
    caption= models.TextField(blank=True)
    width = models.IntegerField(default=0)
    height = models.IntegerField(default=0)
    tags = models.CharField(max_length=255, blank=True)
    created_on = models.DateTimeField(default=datetime.now)

    class Meta:
        ordering = ["-created_on"]
    
    def get_absolute_url(self):
        return "http://vimeo.com/" + str(self.vimeo_id)

    def __str__(self):
        return self.title

    def relative_width(self):
        return 500

    def relative_height(self):
        return (self.relative_width() * self.height) / self.width

    def save(self, *args, **kwargs):
        super(VimeoClip, self).save(*args, **kwargs) # Call the "real" save() method.
        blogitem_save(self, tags=self.tags)

(AMAZON_COM,
 AMAZON_CO_UK) = range(2)
AMAZON_COUNTRIES = ("us", "uk")
AMAZON_CHOICES=[(n, AMAZON_COUNTRIES[n]) for n in range(2)]
AMAZON_URLS=("http://www.amazon.com/gp/product/%s?ie=UTF8&tag=groovymother-20&linkCode=as2&camp=1789&creative=9325&creativeASIN=%s",
             "http://www.amazon.co.uk/gp/product/%s?ie=UTF8&tag=groovymother-21&linkCode=as2&camp=1634&creative=6738&creativeASIN=%s",
             )

class AmazonCD(models.Model):
    blogitem = generic.GenericRelation(BlogItem)
    asin = models.CharField(db_index=True, max_length=15)
    store = models.IntegerField(choices=AMAZON_CHOICES, default=AMAZON_COM)
    title = models.CharField(max_length=255, blank=True)
    artist = models.CharField(max_length=255, blank=True)
    image_url = models.URLField(blank=True)
    comments = models.TextField(blank=True)
    created_on = models.DateTimeField(default=datetime.now)

    class Meta:
        ordering = ["-created_on"]
    
    def get_absolute_url(self):
        return AMAZON_URLS[self.store] % (self.asin, self.asin)

    def __str__(self):
        return self.title

    def save(self, *args, **kwargs):
        if not self.title:
            from threequarters import amazon
            amazon.setLicenseKey("1AGTVVHBTYPBQKT7G482")
            amazon.setLocale(AMAZON_COUNTRIES[int(self.store)])
            res = amazon.ItemLookup(ItemId=self.asin, ResponseGroup="Medium")[0]
            print res.Title.encode('UTF-8')
            self.title = res.Title
            try:
                if hasattr(res, "Artist"):
                    artist = res.Artist
                elif hasattr(res, "Creator"):
                    artist = res.Creator
                if isinstance(artist, list):
                    artist = artist[0]
                self.artist = artist
            except:
                # artist name failed for some reason
                # Audiobook?
                pass
            self.image_url = res.SmallImage.URL

        super(AmazonCD, self).save(*args, **kwargs) # Call the "real" save() method.

        blogitem_save(self)


class Twitter(models.Model):
    blogitem = generic.GenericRelation(BlogItem)
    twitter_id = models.IntegerField(db_index=True)
    starts_with_at = models.BooleanField(default=False)
    description = models.TextField()
    created_on = models.DateTimeField(default=datetime.now)

    class Meta:
        ordering = ["-created_on"]
    
    def get_absolute_url(self):
        return "http://twitter.com/rodbegbie/statuses/%d" % self.twitter_id

    def __str__(self):
        return self.description

    def save(self, *args, **kwargs):
        self.starts_with_at = (self.description and self.description[0] == "@")
        super(Twitter, self).save(*args, **kwargs) # Call the "real" save() method.
        blogitem_save(self)

class LastFMTrack(models.Model):
    blogitem = generic.GenericRelation(BlogItem)
    last_fm_id = models.IntegerField()
    artist = models.CharField(max_length=255)
    title = models.CharField(max_length=255)
    last_fm_url = models.URLField()
    created_on = models.DateTimeField(default=datetime.now)

    class Meta:
        ordering = ["-created_on"]
    
    def get_absolute_url(self):
        return self.last_fm_url

    def __str__(self):
        return self.title

    def save(self, *args, **kwargs):
        super(LastFMTrack, self).save(*args, **kwargs) # Call the "real" save() method.
        blogitem_save(self)

class YelpReview(models.Model):
    blogitem = generic.GenericRelation(BlogItem)
    business = models.CharField(max_length=255)
    review = models.TextField()
    score = models.IntegerField()
    yelp_url = models.URLField()
    created_on = models.DateTimeField(default=datetime.now)

    class Meta:
        ordering = ["-created_on"]
    
    def get_absolute_url(self):
        return self.yelp_url

    def __str__(self):
        return self.business

    def save(self, *args, **kwargs):
        super(YelpReview, self).save(*args, **kwargs) # Call the "real" save() method.
        blogitem_save(self)


# Source: http://sciyoshi.com/blog/2008/aug/27/using-akismet-djangos-new-comments-framework/
from django.contrib.comments.signals import comment_was_posted

def on_comment_was_posted(sender, comment, request, *args, **kwargs):
    # spam checking can be enabled/disabled per the comment's target Model
    #if comment.content_type.model_class() != Entry:
    #    return

    from django.contrib.sites.models import Site
    from django.conf import settings

    try:
        from akismet import Akismet
    except:
        return

    # use TypePad's AntiSpam if the key is specified in settings.py
    if hasattr(settings, "TYPEPAD_ANTISPAM_API_KEY"):
        ak = Akismet(
            key=settings.TYPEPAD_ANTISPAM_API_KEY,
            blog_url='http://%s/' % Site.objects.get(pk=settings.SITE_ID).domain
        )
        ak.baseurl = 'api.antispam.typepad.com/1.1/'
    else:
        ak = Akismet(
            key=settings.AKISMET_API_KEY,
            blog_url='http://%s/' % Site.objects.get(pk=settings.SITE_ID).domain
        )

    if ak.verify_key():
        data = {
            'user_ip': request.META.get('HTTP_X_REAL_IP', '127.0.0.1'),
            'user_agent': request.META.get('HTTP_USER_AGENT', ''),
            'referrer': request.META.get('HTTP_REFERER', ''),
            'comment_type': 'comment',
            'comment_author': comment.user_name.encode('utf-8'),
        }

        if ak.comment_check(comment.comment.encode('utf-8'), data=data, build_data=True):
            comment.flags.create(
                user=comment.content_object.author,
                flag='spam'
            )
            comment.is_public = False
            comment.save()

comment_was_posted.connect(on_comment_was_posted)
