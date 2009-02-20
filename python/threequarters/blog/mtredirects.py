from django.http import HttpResponsePermanentRedirect

MONTHS=("", "jan", "feb", "mar", "apr", "may", "jun", "jul", "aug", "sep", "oct", "nov", "dec")

def entry(request, year, month, day, slug):
    url = "/%s/%s/%s/%s/" % (year, MONTHS[int(month)], day, slug[:15])
    return HttpResponsePermanentRedirect(url)

def month(request, year, month):
    url = "/%s/%s/" % (year, MONTHS[int(month)])
    return HttpResponsePermanentRedirect(url)

def entryById(request, id):
    from models import Legacy
    return HttpResponsePermanentRedirect(Legacy.objects.get(mt_id=int(id)).blogitem_set.get().content_object.get_absolute_url())

def linkarchive(request, year, month, day):
    from datetime import date, timedelta
    weekbegins = date(int(year), int(month), int(day)) + timedelta(days=1)
    return HttpResponsePermanentRedirect("/%s/" % weekbegins.strftime("%Y/week/%W"))

def linktag(request, tag):
    url = "/tag/%s/" % tag
    return HttpResponsePermanentRedirect(url)

