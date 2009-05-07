import xml.etree.cElementTree as ET
from urllib import FancyURLopener
import time, datetime
from threequarters.blog.models import VimeoClip

class MyOpener(FancyURLopener):
    version = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; it; rv:1.8.1.11) Gecko/20071127 Firefox/2.0.0.11 Not-really/I-am-actually-Python'
url = 'http://vimeo.com/api/rodbegbie/clips.xml'
opener = MyOpener()
file = opener.open(url) 
tree = ET.parse(file)
elem = tree.getroot()

for clip in elem.getchildren():
    print clip
    print clip.find('title').text.encode('utf-8')
    clip_id = int(clip.find('clip_id').text)
    (vimeoclip, new) = VimeoClip.objects.get_or_create(vimeo_id=clip_id)
    vimeoclip.title = clip.find('title').text
    vimeoclip.caption = clip.find('caption').text or ""
    vimeoclip.width = int(clip.find('width').text)
    vimeoclip.height = int(clip.find('height').text)
    vimeoclip.tags = clip.find('tags').text or ""
    vimeoclip.created_on = datetime.datetime(*(time.strptime(clip.find('upload_date').text, "%Y-%m-%d %H:%M:%S")[0:6])) - datetime.timedelta(hours=3)
    vimeoclip.save()
