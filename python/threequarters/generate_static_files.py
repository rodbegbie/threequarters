#!/usr/bin/env python

from staticgenerator import quick_publish, StaticGeneratorException
from django.contrib.flatpages.models import FlatPage
from threequarters.blog.models import *

for page in FlatPage.objects.all():
    try:
        print page
        quick_publish(page)
    except StaticGeneratorException, e:
        print "BANG", e.message



for tag in Tag.objects.exclude(display__contains="fast food restaurant"):
    try:
        print tag.tag
        quick_publish(tag)
    except StaticGeneratorException, e:
        print "BANG", e.message

for post in Post.objects.all():
    try:
        print post.title
        quick_publish(post)
    except StaticGeneratorException, e:
        print "BANG", e.message

for link in Link.objects.all():
    try:
        print link
        quick_publish(link.get_comments_url())
    except StaticGeneratorException, e:
        print "BANG", e.message


for year in (2002, 2003, 2004, 2005, 2006, 2007, 2008, 2009, 2010, 2011, 2012):
    for week in range(0, 53):
        try:
            quick_publish('/%d/week/%d/' % (year, week))
        except StaticGeneratorException, e:
            print "BANG", e.message

    for mon in ('jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'):
        try:
            quick_publish('/%d/%s/' % (year, mon))
        except StaticGeneratorException, e:
            print "BANG", e.message

        for day in range(1,32):
            try:
                quick_publish('/%d/%s/%d/' % (year, mon, day))
            except StaticGeneratorException, e:
                print "BANG", e.message

