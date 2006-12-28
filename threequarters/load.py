#!/usr/bin/python

from threequarters.blog.models import *

BlogItem.objects.all().delete()
Legacy.objects.all().delete()
Post.objects.all().delete()
Link.objects.all().delete()
TaggedItem.objects.all().delete()

# import MySQL module
import MySQLdb
# connect
db = MySQLdb.connect(host="localhost", user="mt33", passwd="33tm", db="mt33")
# create a cursor
cursor = db.cursor()
# execute SQL statement
cursor.execute("""SELECT entry_id, 
                    entry_status, 
                    entry_title, 
                    entry_text, 
                    entry_text_more,
                    entry_created_on,
                    entry_modified_on,
                    entry_basename
                  FROM mt_entry
                  WHERE entry_blog_id = 10
                  ORDER BY entry_id """)
# get the resultset as a tuple
result = cursor.fetchall()
# iterate through resultset
for record in result:
    print record[0] , "-->", record[2]
    post = Post()
    post.title = record[2]
    post.slug = record[7]
    body = record[3]
    if record[4]:
        body = body + record[4]
    post.body_textile = body
    
    post.created_on = record[5]
    post.modified_on = record[6]
    post.draft = record[1] <> 2
    post.save()

    legacy = Legacy(mt_id=record[0], basename=record[7])
    legacy.save()
    
    blogitem = post.blogitem.get()
    blogitem.legacy = legacy
    blogitem.save()

cursor.execute("""SELECT entry_id, 
                    entry_excerpt,
                    entry_title, 
                    entry_text, 
                    entry_text_more,
                    entry_created_on,
                    entry_modified_on,
                    entry_basename
                  FROM mt_entry
                  WHERE entry_blog_id = 6
                  ORDER BY entry_id """)
# get the resultset as a tuple
result = cursor.fetchall()
# iterate through resultset
for record in result:
    print record[0] , "-->", record[2]
    post = Link()
    post.title = record[2]
    post.description = record[3]
    post.url = record[4]
    post.slug = record[7]

    via = ""
    if record[1]:
        via = record[1]
    post.via = via

    post.created_on = record[5]
    post.modified_on = record[6]


    cursor2 = db.cursor()
    cursor2.execute("""SELECT tagmap_input
                      FROM mt_tagmap
                      WHERE tagmap_object_id = %d
                      """ % record[0])
    # get the resultset as a tuple
    result2 = cursor2.fetchall()
    # iterate through resultset
    tags = []
    for record2 in result2:
        tags.append(record2[0])

    post.tags = ", ".join(tags)
    print "TAGS!", post.tags
    post.save()

    legacy = Legacy(mt_id=record[0], basename=record[7])
    legacy.save()
    
    blogitem = post.blogitem.get()
    blogitem.legacy = legacy
    blogitem.save()

