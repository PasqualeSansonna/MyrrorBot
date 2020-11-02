from flask import Flask, request
from flask_restful import Resource, Api


app = Flask(__name__)
api = Api(app)

app.debug = True

"""
http://localhost:5003/exp/
?mood=bad
&stress=yes
&depression=yes
&bmi=over
&activity=high
&goal=gain
&sleep=good
&restr=vegetarian%2Clactosefree%2Cglutenfree%2Clownichel%2Clight
&prob=heart%2Cdiabete%2Cjoint%2Cpressure%2Cchol&imgurlAhttps%3A%2F%2Fwww.giallozafferano.it%2Fimages%2Fricette%2F173%2F17300%2Ffoto_hd%2Fhd650x433_wm.jpgimgurlBhttps%3A%2F%2Fwww.giallozafferano.it%2Fimages%2Fricette%2F182%2F18220%2Ffoto_hd%2Fhd650x433_wm.jpg&difficulty=3
"""
class Explain(Resource):
        
        def get(self):
                r = request.args.get("restr")
                #p = request.args.get("prob")
                print(r)
                #print(p)
                return r

api.add_resource(Explain, '/exp/')

if __name__ == '__main__':
     app.run(port=5003)
