class Weight(models.Model):
    """(Weight description)"""
    weight = models.FloatField(max_digits=5, decimal_places=1)
    underwear = models.BooleanField(default=True)
    jeans = models.BooleanField(default=False)
    time = models.DateTimeField(blank=True, default=datetime.datetime.new())

    class Admin:
        list_display = ('time', 'weight')

    def __str__(self):
        return "Weight"

class ShangriLaOil(models.Model):
    """(ShangriLaOil description)"""
    
    tablespoons = models.FloatField(max_digits=3, decimal_places=1)
    time = models.DateTimeField(blank=True, default=datetime.datetime.new())

    class Admin:
        list_display = ('time','tablespoons')

    def __str__(self):
        return "ShangriLaOil"

