from django.core.serializers.json import simplejson
from urllib import urlopen
import time, datetime
from threequarters.blog.models import Twitter

url = 'http://twitter.com/statuses/user_timeline/rodbegbie.json?count=5'
json = urlopen(url).read()

for entry in simplejson.loads(json):
    desc = entry["text"].encode('utf-8')
    desc = desc.replace("&quot;", '"')
    print desc
    (twitter, new) = Twitter.objects.get_or_create(twitter_id = entry["id"])
    twitter.description = desc
    twitter.created_on = datetime.datetime(*(time.strptime(entry["created_at"], "%a %b %d %H:%M:%S +0000 %Y")[0:6])) - datetime.timedelta(hours=4)
    #twitter.created_on = datetime.datetime(*(time.strptime(entry["created_at"], "%a %d %b %H:%M:%S +0000 %Y")[0:6])) - datetime.timedelta(hours=4)
    #twitter.created_on = datetime.datetime(*(time.strptime(entry["created_at"], "%m/%d/%Y %H:%M:%S UTC")[0:6])) - datetime.timedelta(hours=4)
    twitter.save()

