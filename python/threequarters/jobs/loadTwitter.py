from django.core.serializers.json import simplejson
from urllib import urlopen
import time, datetime
from threequarters.blog.models import Twitter

url = 'http://twitter.com/statuses/user_timeline/rodbegbie.json?count=10'
json = urlopen(url).read()

for entry in simplejson.loads(json):
    desc = entry["text"].encode('utf-8')
    desc = desc.replace("&quot;", '"')
    desc = desc.replace("&amp;", '&')
    print desc
    (twitter, new) = Twitter.objects.get_or_create(twitter_id = entry["id"])
    if new or twitter.description != desc:
    	twitter.description = desc
        twitter.created_on = datetime.datetime(*(time.strptime(entry["created_at"], "%a %b %d %H:%M:%S +0000 %Y")[0:6])) - datetime.timedelta(hours=8)
        twitter.save()

