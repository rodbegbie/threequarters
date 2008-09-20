from django.db.models.fields import IntegerField
from django.conf import settings

class BigIntegerField(IntegerField):
	empty_strings_allowed=False
	def get_internal_type(self):
		return "BigIntegerField"
	
	def db_type(self):
		if settings.DATABASE_ENGINE == oracle:
			return 'NUMBER(19)'
		else:
			return 'bigint'

