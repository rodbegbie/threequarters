from django.http import HttpResponsePermanentRedirect

MONTHS=("", "jan", "feb", "mar", "apr", "may", "jun", "jul", "aug", "sep", "oct", "nov", "dec")

def entry(request, year, month, day, slug):
    url = "/%s/%s/%s/%s/" % (year, MONTHS[int(month)], day, slug[:15])
    return HttpResponsePermanentRedirect(url)

def linkarchive(request, year, month, day):
    from datetime import date, timedelta
    weekbegins = date(int(year), int(month), int(day)) + timedelta(days=1)
    return HttpResponsePermanentRedirect("/%s/" % weekbegins.strftime("%Y/week/%W"))

def linktag(request, tag):
    url = "/tag/%s/" % tag
    return HttpResponsePermanentRedirect(url)

