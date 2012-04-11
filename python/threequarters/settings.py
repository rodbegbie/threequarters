# Django settings for threequarters project.

INTERNAL_IPS=('69.17.48.8',)
DEBUG = False #True
TEMPLATE_DEBUG = DEBUG

EMAIL_HOST = "flaming.arsecandle.org"
EMAIL_USE_TLS = True
SERVER_EMAIL = DEFAULT_FROM_EMAIL = "threequarters@arsecandle.org"

ADMINS = (
    ('Rod Begbie', 'rod@arsecandle.org'),
)

MANAGERS = ADMINS

DATABASES = {
    'default': {
        'ENGINE': 'django.db.backends.mysql',
        'NAME': 'threequarters',
        'USER': 'threequarters',
        'PASSWORD': 'sretrauqeerht',
    }
}

# Local time zone for this installation. All choices can be found here:
# http://www.postgresql.org/docs/current/static/datetime-keywords.html#DATETIME-TIMEZONE-SET-TABLE
TIME_ZONE = 'America/Los_Angeles'

# Language code for this installation. All choices can be found here:
# http://www.w3.org/TR/REC-html40/struct/dirlang.html#langcodes
# http://blogs.law.harvard.edu/tech/stories/storyReader$15
LANGUAGE_CODE = 'en-us'

SITE_ID = 1

# Absolute path to the directory that holds media.
# Example: "/home/media/media.lawrence.com/"
STATIC_ROOT = '/var/www/threequarters/htdocs/'

# URL that handles the media served from MEDIA_ROOT.
# Example: "http://media.lawrence.com"
STATIC_URL = 'http://static.groovymother.com'

# Make this unique, and don't share it with anybody.
SECRET_KEY = 'y03j!q8vw$2n4o)37x+*ijz08i%$3%$ik%7y)zl%5pqfpf^s3w'

# List of callables that know how to import templates from various sources.
TEMPLATE_LOADERS = (
    'django.template.loaders.filesystem.Loader',
    'django.template.loaders.app_directories.Loader',
    'django.template.loaders.eggs.Loader',
)

MIDDLEWARE_CLASSES = (
    'django.middleware.http.ConditionalGetMiddleware',
    #'threequarters.middleware.PsycoMiddleware',
    'threequarters.middleware.SetRemoteAddrFromForwardedFor',
    'django.middleware.common.CommonMiddleware',
    'django.middleware.gzip.GZipMiddleware',
    'django.middleware.csrf.CsrfViewMiddleware',
    'django.contrib.sessions.middleware.SessionMiddleware',
    'django.contrib.auth.middleware.AuthenticationMiddleware',
    #'django.middleware.cache.CacheMiddleware',
    'django.contrib.flatpages.middleware.FlatpageFallbackMiddleware',
    'django.contrib.redirects.middleware.RedirectFallbackMiddleware',
    'django.contrib.messages.middleware.MessageMiddleware',
)

ROOT_URLCONF = 'threequarters.urls'

INSTALLED_APPS = (
    'threequarters.blog',
    'django.contrib.comments',
    'django.contrib.flatpages',
    'django.contrib.redirects',
    'django.contrib.admin',
    'django.contrib.auth',
    'django.contrib.contenttypes',
    'django.contrib.markup',
    'django.contrib.sessions',
    'django.contrib.sitemaps',
    'django.contrib.sites',
)

TEMPLATE_DIRS = (
    # Put strings here, like "/home/html/django_templates".
    # Always use forward slashes, even on Windows.
    "./templates",
    "/var/www/threequarters/python/threequarters/templates",
)

AKISMET_API_KEY="d7b8758ca65f"
#TYPEPAD_ANTISPAM_API_KEY="55170912e6c191cbe7621ba4f173a809"
COMMENTS_ALLOW_PROFANITIES=True

# Cache Settings
CACHE_BACKEND = 'memcached://127.0.0.1:11211/'
CACHE_MIDDLEWARE_SECONDS = 300
CACHE_MIDDLEWARE_ANONYMOUS_ONLY = True

TEMPLATE_CONTEXT_PROCESSORS = ("django.contrib.auth.context_processors.auth",
"django.core.context_processors.debug",
"django.core.context_processors.i18n",
"django.core.context_processors.request",
)

#USE_ETAGS = True

WHOOSH_INDEX_DIR = "/var/www/threequarters/whooshindex"

HOPTOAD_API_KEY = "557511cf628485173cb72c1f7be68166"
