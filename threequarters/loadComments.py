#!/usr/bin/python

from threequarters.blog.models import *
from threequarters.comments.models import *

FreeComment.objects.all().delete()

# import MySQL module
import MySQLdb
# connect
db = MySQLdb.connect(host="localhost", user="mt33", passwd="33tm", db="mt33")
# create a cursor
cursor = db.cursor()
# execute SQL statement
cursor.execute("""SELECT comment_entry_id, 
                    comment_ip, 
                    comment_author, 
                    comment_email,
                    comment_url,
                    comment_text,
                    comment_created_on
                  FROM mt_comment
                  WHERE comment_blog_id = 1
                  AND comment_junk_status <> -1
                  ORDER BY comment_entry_id """)
# get the resultset as a tuple
result = cursor.fetchall()
# iterate through resultset
for record in result:
    print record[0] , "-->", record[2]
    blogitem = Legacy.objects.get(mt_id=record[0]).blogitem_set.get()
    comment = FreeComment(content_type_id=13, object_id=blogitem.id, site_id=1, is_public=True)
    comment.ip_address = record[1] or "0.0.0.0"
    comment.person_name = record[2]
    comment.person_email = record[3] or "unknown"
    comment.person_url = record[4] or ""
    comment.comment = record[5]
    comment.submit_date = record[6]
    comment.approved=False
    comment.save()

