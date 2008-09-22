CONSUMER_SECRET='gQYRSEgCsEZSCDM2Pa6qr9aTVCmT80Mx'
CONSUMER_KEY='3q3VkoxUjwP9'

USER_TOKEN_STRING='oauth_token_secret=8gMH42725VgaLB4b3iahYLdxozVawLlM&oauth_token=VhfSR2SzLkzM'

from fireeagle_api import FireEagle
from oauth import OAuthToken

from threequarters.blog.models import Location
import datetime

fe = FireEagle(CONSUMER_KEY, CONSUMER_SECRET)
token = OAuthToken.from_string(USER_TOKEN_STRING)

locations = fe.user(token)
#from pprint import pprint
#pprint(locations)

lastloc = Location.objects.all()[0]
for location in locations:
    if location["level"] == 3: # CITY
        name = location["name"]
	located_at = location["located_at"] 
	#print "LOCATED_AT", located_at, "LAST", lastloc.located_at
	if name <> lastloc.name:
	    print "ADDING LOCATION", name
            loc = Location(name=name, located_at=located_at)
	    loc.save()
