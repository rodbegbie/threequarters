from threequarters.blog.models import FlickrPhoto
from threequarters import flickr
import datetime
import re

flickr.API_KEY='1866fbe3d625142f11c545d7b881d511'
machinetag = re.compile(".+:.+=.+")

photos = flickr.people_getPublicPhotos(user_id="35034351963@N01", per_page=10)

for photo in photos:
    print photo.title.encode('utf-8'), photo.dateposted
    (flickrphoto, new) = FlickrPhoto.objects.get_or_create(flickr_id = photo.id)
    flickrphoto.title = photo.title.encode('utf-8')
    flickrphoto.description = photo.description.encode('utf-8')
    flickrphoto.created_on = datetime.datetime.fromtimestamp(float(photo.dateposted))
    
    sizes = photo.getSizes()
    for size in sizes:
        if size["label"] == "Small":
            flickrphoto.image_url = size["source"]
            flickrphoto.image_height = size["height"]
            flickrphoto.image_width = size["width"]

    flickrphoto.flickr_url = "http://flickr.com/photos/groovymother/%s/" % photo.id

    if photo.tags:
        tags = []
        for tag in photo.tags:
            if not machinetag.match(tag.raw):
	        print tag.raw, repr(tag.raw)
                tags.append(tag.raw)
    
        flickrphoto.tags = u", ".join(tags)

    flickrphoto.save()
