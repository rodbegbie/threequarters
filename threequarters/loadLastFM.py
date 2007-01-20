import xml.etree.cElementTree as ET
from urllib import urlopen
import time, datetime
from threequarters.blog.models import LastFMTrack

url = 'http://ws.audioscrobbler.com/1.0/user/rodbegbie/recenttracks.xml'
file = urlopen(url) 
tree = ET.parse(file)
elem = tree.getroot()

for track in elem.getchildren():
    print track.find('name').text
    uts = int(track.find('date').get('uts'))
    (lastfmtrack, new) = LastFMTrack.objects.get_or_create(last_fm_id=uts)
    lastfmtrack.artist = track.find('artist').text
    lastfmtrack.title = track.find('name').text
    lastfmtrack.last_fm_url = track.find('url').text
    lastfmtrack.created_on = datetime.datetime.fromtimestamp(uts)
    lastfmtrack.save()
