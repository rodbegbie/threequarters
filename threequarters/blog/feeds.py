from threequarters.blog.models import *
from django.http import HttpResponse
from django.utils.xmlutils import SimplerXMLGenerator
from django.utils.feedgenerator import rfc3339_date
from django.utils.html import linebreaks
from datetime import timedelta

def feed(request, linksonly=False):
    if linksonly:
        blogitems = BlogItem.objects.filter(content_type__model="link").order_by('-created_on')[:30]
        selfurl = "http://groovymother.com/links/index.atom"
    else:
        blogitems = BlogItem.objects.exclude(content_type__model="twitter").exclude(content_type__model="lastfmtrack").order_by('-created_on')[:30]
        selfurl = "http://groovymother.com/index.atom"

    from StringIO import StringIO
    response = StringIO()
    handler = SimplerXMLGenerator(response, 'utf-8')
    handler.startDocument()
    handler.startElement(u"feed", {u"xmlns": u"http://www.w3.org/2005/Atom",
                                   u"xml:lang": u"en",
                                   u"xml:base": u"http://groovymother.com/"})

    handler.addQuickElement(u"title", u"&#8220;groovy mother&#8221;", {u"type": u"html"})
    handler.addQuickElement(u"subtitle", u"Rod Begbie's home for the propagation of hypertextual nonsense")
    handler.addQuickElement(u"icon", u"http://static.groovymother.com/images/avatar80.png")
    
    handler.startElement(u"author", {})
    handler.addQuickElement(u"name", u"Rod Begbie")
    handler.addQuickElement(u"uri", u"http://www.begbie.com/")
    handler.addQuickElement(u"email", u"rod@groovymother.com")
    handler.endElement(u"author")

    handler.addQuickElement(u"id", u"tag:arsecandle.org,2002:groovymother")
    handler.addQuickElement(u"generator", u"ThreeQuarters 0.5")
    handler.addQuickElement(u"rights", u"Copyright (c) 2006, Rod Begbie")
    handler.addQuickElement(u"link", None, {u"rel": u"self",
                                            u"href": selfurl,
                                            u"type": u"application/atom+xml"})

    handler.addQuickElement(u"updated", rfc3339_date(blogitems[0].created_on+timedelta(hours=5)).decode("ascii"))
   
    for item in blogitems:
        if item.content_type.model == "post":
            post = item.content_object
            handler.startElement(u"entry", {})
            handler.addQuickElement(u"title", post.title)
            handler.addQuickElement(u"id", "tag:groovymother.com,%s:%s"%(post.created_on.strftime('%Y-%m-%d'), post.id))
            
            handler.addQuickElement(u"content", post.body_xhtml, {u"type": u"html"})

            handler.addQuickElement(u"link", None,
                                    { u"rel": "alternate",
                                      u"type": u"text/html",
                                      u"href": 'http://groovymother.com' + post.get_absolute_url()
                                    })

            handler.addQuickElement(u"published", rfc3339_date(post.created_on+timedelta(hours=5)).decode("ascii"))
            handler.addQuickElement(u"updated", rfc3339_date(post.modified_on+timedelta(hours=5)).decode("ascii"))

            for tag in item.tags.all():
                handler.addQuickElement(u"category", None,
                                        { u"scheme": u"http://groovymother.com/tag/",
                                          u"term": tag.tag,
                                          u"label": tag.display
                                        })
            handler.endElement(u"entry")

        elif item.content_type.model == "link":
            link = item.content_object
            handler.startElement(u"entry", {})
            handler.addQuickElement(u"title", link.title)
            handler.addQuickElement(u"summary", link.description, {u"type": u"text"})
            handler.addQuickElement(u"id", "tag:groovymother.com,%s:%s"%(link.created_on.strftime('%Y-%m-%d'), link.id))

            handler.addQuickElement(u"link", None,
                                    { u"rel": "alternate",
                                      u"type": u"text/html",
                                      u"href": 'http://groovymother.com' + link.get_absolute_url()
                                    })
            handler.addQuickElement(u"link", None,
                                    { u"rel": "related",
                                      u"type": u"text/html",
                                      u"href": link.url
                                    })
            if link.via:
                handler.addQuickElement(u"link", None,
                                        { u"rel": "via",
                                          u"title": "[via]",
                                          u"type": u"text/html",
                                          u"href": link.via
                                        })

            handler.addQuickElement(u"published", rfc3339_date(link.created_on+timedelta(hours=5)).decode("ascii"))
            handler.addQuickElement(u"updated", rfc3339_date(link.modified_on+timedelta(hours=5)).decode("ascii"))

            for tag in item.tags.all():
                handler.addQuickElement(u"category", None,
                                        { u"scheme": u"http://groovymother.com/tag/",
                                          u"term": tag.tag,
                                          u"label": tag.display
                                        })
            handler.endElement(u"entry")


        elif item.content_type.model == "flickrphoto":
            photo = item.content_object
            handler.startElement(u"entry", {})
            handler.addQuickElement(u"title", photo.title)
            handler.addQuickElement(u"id", "tag:flickr.com,2004:%s" % photo.flickr_id)
            content = """<img src="%s">""" % photo.image_url + linebreaks(photo.description)
            handler.addQuickElement(u"content", content, {u"type": u"html"})

            handler.addQuickElement(u"link", None,
                                    { u"rel": "alternate",
                                      u"type": u"text/html",
                                      u"href": photo.get_absolute_url()
                                    })
            handler.addQuickElement(u"published", rfc3339_date(photo.created_on+timedelta(hours=5)).decode("ascii"))
            handler.addQuickElement(u"updated", rfc3339_date(photo.created_on+timedelta(hours=5)).decode("ascii"))
            for tag in item.tags.all():
                handler.addQuickElement(u"category", None,
                                        { u"scheme": u"http://groovymother.com/tag/",
                                          u"term": tag.tag,
                                          u"label": tag.display
                                        })
            handler.endElement(u"entry")

        elif item.content_type.model == "amazoncd":
            cd = item.content_object
            handler.startElement(u"entry", {})
            handler.addQuickElement(u"title", u"CD Purchase: %s - %s" % (cd.artist, cd.title))
            handler.addQuickElement(u"id", "tag:groovymother.com,%s:%s"%(cd.created_on.strftime('%Y-%m-%d'), cd.id))
            content = """<a href="%s"><img src="%s"><br>%s - %s</a>""" % (cd.get_absolute_url(), cd.image_url, cd.artist, cd.title)
            handler.addQuickElement(u"content", content, {u"type": u"html"})

            handler.addQuickElement(u"link", None,
                                    { u"rel": "alternate",
                                      u"type": u"text/html",
                                      u"href": cd.get_absolute_url()
                                    })
            handler.addQuickElement(u"published", rfc3339_date(cd.created_on+timedelta(hours=5)).decode("ascii"))
            handler.addQuickElement(u"updated", rfc3339_date(cd.created_on+timedelta(hours=5)).decode("ascii"))
            handler.endElement(u"entry")

    handler.endElement(u"feed")
    return HttpResponse(response.getvalue(), mimetype="application/atom+xml")
