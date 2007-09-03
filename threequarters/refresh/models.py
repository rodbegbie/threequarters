from django.db import models

class Weight(models.Model):
    """(Weight description)"""
    weight = models.FloatField(max_digits=5, decimal_places=1)
    underwear = models.BooleanField(default=True)
    jeans = models.BooleanField(default=False)
    time = models.DateTimeField(auto_now_add=True)

    class Admin:
        list_display = ('time', 'weight')

    def __str__(self):
        return "Weight: %.1flbs" % self.weight

class ShangriLaOil(models.Model):
    """(ShangriLaOil description)"""
    
    tablespoons = models.FloatField(max_digits=3, decimal_places=1)
    time = models.DateTimeField(auto_now_add=True)

    class Admin:
        list_display = ('time','tablespoons')

    def __str__(self):
        return "ShangriLaOil"

class BodyFat(models.Model):
    """(BodyFat description)"""
    
    percent = models.FloatField(max_digits=3, decimal_places=1)
    time = models.DateTimeField(auto_now_add=True)

    class Admin:
        list_display = ('time', 'percent')

    def __str__(self):
        return "BodyFat: %.1f%%" % self.percent
