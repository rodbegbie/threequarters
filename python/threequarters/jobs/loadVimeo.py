import xml.etree.cElementTree as ET
from urllib import urlopen
import time, datetime
from threequarters.blog.models import VimeoClip

url = 'http://vimeo.com/api/rodbegbie/clips.xml'
file = urlopen(url) 
tree = ET.parse(file)
elem = tree.getroot()

for clip in elem.getchildren():
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
