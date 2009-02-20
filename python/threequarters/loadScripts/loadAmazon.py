#!/usr/bin/python

from threequarters.blog.models import *

AmazonCD.objects.all().delete()

from threequarters import amazon
amazon.setLicense("1AGTVVHBTYPBQKT7G482")

# import MySQL module
import MySQLdb
# connect
db = MySQLdb.connect(host="localhost", user="mt33", passwd="33tm", db="mt33")
# create a cursor
cursor = db.cursor()
# execute SQL statement
cursor.execute("""SELECT mediamanager_isbn,
                    mediamanager_created_on,
                    mediamanager_modified_on
                  FROM mt_mediamanager
                  WHERE mediamanager_catalog = 'Music'
                  AND mediamanager_blog_id = 1
                  """)
# get the resultset as a tuple
result = cursor.fetchall()
# iterate through resultset
for record in result:
    print record[0]
    if record[0][0] == 'u':
        asin = record[0][1:]
        store = AMAZON_CO_UK
    else:
        asin = record[0]
        store = AMAZON_COM

    cd = AmazonCD(asin=asin, store=store, created_on=record[1])
    cd.save()

