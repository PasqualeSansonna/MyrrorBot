from flask import Flask, request
from flask_restful import Resource, Api

import re
import csv
import sys
import json
import random
import numpy as np
import pandas as pd
from random import choice


app = Flask(__name__)
api = Api(app)

app.debug = True

class Explain(Resource):
    def get(self):

        #---
        # Explanation functions definitions
        #---

        """
        The Explanation function Popularity returns a static string
        saying that the recipe is very popular in the community.
        """
        def popularity(recipeA_name):
            explanation = ""
            explanation = "I suggest you " + recipeA_name + \
                " since it is very popular in the community."
            return explanation

        """
        The explanation function foodGoals_one take in input the name of the recipe,
        the user goal (lose -> lose weight, gain -> gain weight, no -> no goals)
        and the recipe calories. It returns a string saying that the recipe is
        good related to the goal of the user.
                    
        """
        def foodGoals_one(recipeA_name, user_goal, recipeA_calories):
            explanation = ""
            explanation = recipeA_name + " has " + recipeA_calories + " calories. "
            
            if(user_goal == "lose"):
                explanation += "It is a good choice, since you want to lose weight."
            elif(user_goal == "gain"):
                explanation += "It is a good choice, since you want to gain weight."
            elif(user_goal == "no"):
                explanation += "The average calorie intake for a person like you is 1900 calories."
            return explanation

        """
        The explanation function foodGoals_two compares the amount of calories of recipe A
        and Recipe B, and based on the user goal
        (lose -> lose weight, gain -> gain weight, no -> no goals)
        suggests the better recipe in terms of calories. If the user has no goals, then
        the output will be only the comparison of the amount of calories.
        """
        def foodGoals_two(user_goal, recipeA_name, recipeB_name, recipeA_calories, recipeB_calories):
            explanation = ""

            if(recipeA_calories > recipeB_calories):
                if(user_goal == "lose"):
                    explanation = recipeB_name + " has less calories (" + str(recipeB_calories) + " Kcal) than " + recipeA_name + " (" + str(recipeA_calories) + " Kcal). "
                    explanation += "It can help you reaching your goal of losing weight."

                elif(user_goal == "gain"):
                    explanation = recipeA_name + " has more calories (" + str(recipeA_calories) + " Kcal) than " + recipeB_name + " (" + str(recipeB_calories) + " Kcal). "
                    explanation += "It can help you reaching your goal of gaining weight."

                elif(user_goal == "no"):
                    explanation = recipeA_name + " has more calories (" + str(recipeA_calories) + " Kcal) than " + recipeB_name + " (" + str(recipeB_calories) + " Kcal). "
                    explanation += "The average calorie intake for a person like you is 1900 calories."

            elif(recipeA_calories < recipeB_calories):

                if(user_goal == "lose"):
                    explanation = recipeA_name + " has less calories (" + str(recipeA_calories) + " Kcal) than " + recipeB_name + " (" + str(recipeB_calories) + " Kcal). "
                    explanation += "It can help you reaching your goal of losing weight."

                elif(user_goal == "gain"):
                    explanation = recipeB_name + " has more calories (" + str(recipeB_calories) + " Kcal) than " + recipeA_name + " (" + str(recipeA_calories) + " Kcal). "
                    explanation += "It can help you reaching your goal of gaining weight."

                elif(user_goal == "no"):
                    explanation = recipeB_name + " has more calories (" + str(recipeB_calories) + " Kcal) than " + recipeA_name + " (" + str(recipeA_calories) + " Kcal). "
                    explanation += "The average calorie intake for a person like you is 1900 calories."

            else:
                explanation = recipeA_name + " is as caloric as " + recipeB_name + ". Both recipes have " + str(recipeA_calories) + " calories (Kcal)."
                explanation += "The average calorie intake for a person like you is 1900 calories."
                
            
            return explanation
        """
        The explanation function foodPreferences_one connects the recipe A with the user
        restriction (vegetarian, lactosefree, glutenfree, lownickel, light).
        The output will be a static string saying that the recipe is suggested because
        of a random restriction chosen among the user preferences.
        """ 
        def foodPreferences_one(name_restriction, description, recipeA_name):
            explanation = "I suggest you " + recipeA_name

            explanation += " because you want " + \
                           name_restriction + " recipes and " + description

            return explanation

        """
        The explanation function foodPreferences_two connects Recipe A and Recipe B
        with the user restrictions. The output will be a static string saying
        that the recipes are suggested because
        of a random restriction chosen among the user preferences.
        """
        def foodPreferences_two(name_restriction, description):

            explanation = "I suggest you these recipes " + \
                          "because you want " + \
                          name_restriction + " recipes and " + description

            return explanation

        """
        The explanation function foodFeatures_one outputs the amount of macronutrients
        with labels "low", "medium", and "high". We used the FSA table to set the ranges
        of each macronutrient.
        """

        def foodFeatures_one(recipeA, nutrients):
            explanation = ""
            small = ""
            great = ""
            smallList = []
            greatList = []
            listNutrients = list(nutrients.keys())
            random.shuffle(listNutrients)

            recipeA_name = recipeA["title"]

            for item in listNutrients:
                if(not(np.isnan(recipeA[item]))):
                    if(recipeA[item] <= nutrients[item]["RI"]):
                        smallList.append(item)
                    else:
                        greatList.append(item) 

            if smallList != []:
                small = smallList[0]
                strSmall = small
                
                if(small == "saturatedFat"):
                    strSmall = "saturated fats"
         
                explanation = recipeA_name + " has a small amount of " + strSmall + " (" + str(recipeA[small]) + " gr)" + \
                              " than reference daily intake (" + str(nutrients[small]["RI"]) + " gr)"
            
            if greatList != []:
                great = greatList[0]
                strGreat = great

                if(great == "saturatedFat"):
                    strGreat = "saturated fats"
                    
                if(small != ""):
                    explanation += " and an excess of " + strGreat + " (" + str(recipeA[great]) + " gr)" + \
                                      " than reference daily intake (" + str(nutrients[great]["RI"]) + " gr)"
                else:
                    explanation += recipeA_name + " has an excess of " + great + " (" + str(recipeA[great]) + " gr)" + \
                                      " than reference daily intake (" + str(nutrients[great]["RI"]) + " gr)"

            return explanation, small, great
           

        """
        The explanation function foodFeatures_two compares nutrients (calories
        and sugars) of the two recipes. 

        """
        def foodFeatures_two(recipeA, recipeB, nutrients):

            recipeA_name = recipeA["title"]
            recipeB_name = recipeB["title"]
            explanation = ""
            listNutrients = list(nutrients.keys())
            random.shuffle(listNutrients)
            smallList = []
            greatList = []

            for item in listNutrients:
                if(not(np.isnan(recipeA[item])) and not(np.isnan(recipeB[item]))):
                    if(recipeA[item] < recipeB[item]):
                        smallList.append(item)
                    elif(recipeA[item] > recipeB[item]):
                        greatList.append(item)
            
            if(smallList != []):
                small = smallList[0]
                strSmall = small
                
                if(small == "saturatedFat"):
                    strSmall = "saturated fats"
                    
                explanation = recipeA_name + " has a lower amount of " + strSmall + " (" + str(recipeA[small]) + " gr)"

            if(greatList != []):
                great = greatList[0]
                strGreat = great

                if(great == "saturatedFat"):
                    strGreat = "saturated fats"

                    
                if(small != "" and great != ""):
                    explanation += " and a higher amount of " + strGreat + " (" + str(recipeA[great]) + " gr)"
                    explanation += " than " + recipeB_name + " (" + small + ": " + str(recipeB[small]) + " gr, " + great + ": " + str(recipeB[great]) + " gr)"
                elif(great != ""):
                    explanation += recipeB_name + " has a higher amount of " + great + " (" + str(recipeB[great]) + " gr)"
                    explanation += " than " + recipeA_name + " (" + great + ": " + str(recipeA[great]) + " gr)"    

            return explanation,small,great

        """
        The explanation function userSkills_one take as input the user cooking experience
        and returns a string saying that the recipe is rated by the user in a certain
        difficulty, and it is adequate with the specific user cooking experience.
        """
        def userSkills_one(user_exp, recipe_name):
            explanation = ""
            if(user_exp == 1):
                explanation = recipe_name + " is rated by the users as very simple to prepare, " + \
                              "and it is adequate to your very low cooking experience."
            elif(user_exp == 2):
                explanation = recipe_name + " is rated by the users as simple to prepare, " + \
                              "and it is adequate to your low cooking experience."
            elif(user_exp == 3):
                explanation = recipe_name + " is rated by the users as quite simple to prepare, " + \
                              "and it is adequate to your medium cooking experience."
            elif(user_exp == 4):
                explanation = recipe_name + " is rated by the users as difficult to prepare, " + \
                              "and it is adequate to your high cooking experience."
            elif(user_exp == 5):
                explanation = recipe_name + " is rated by the users as very difficult to prepare, " + \
                              "and it is adequate to your very high cooking experience."
            return explanation


        """
        The explanation function userSkills_two takes as input the user cooking skills and
        the difficulty of the two recommended recipes. These difficulties are converted
        in a numeric format in order to make a comparison and suggest the better recipe in
        terms of user skills and recipe difficulty.
        """
        def userSkills_two(user_skills, recipeA, recipeB, diffA, diffB):
            # molto facile, facile, media, difficile, molto difficile

            explanation = ""

            if(diffA == "molto facile"):
                diffA = 1
            elif(diffA == "facile"):
                diffA = 2
            elif(diffA == "media"):
                diffA = 3
            elif(diffA == "difficile"):
                diffA = 4
            elif(diffA == "molto difficile"):
                diffA = 5

            if(diffB == "molto facile"):
                diffB = 1
            elif(diffB == "facile"):
                diffB = 2
            elif(diffB == "media"):
                diffB = 3
            elif(diffB == "difficile"):
                diffB = 4
            elif(diffB == "molto difficile"):
                diffB = 5

            if(user_skills == 1):
                if(diffA < diffB):
                    explanation = recipeA + " is rated by the users as easier to prepare than " + recipeB + \
                                  ", and this is adequate with your very low cooking experience."
                elif(diffB < diffA):
                    explanation = recipeB + " is rated by the users as easier to prepare than " + recipeA + \
                                  ", and this is adequate with your very low cooking experience."
                else:
                    explanation = recipeA + " is as easy to prepare as " + recipeB + "."

            if(user_skills == 2):
                if(diffA < diffB):
                    explanation = recipeA + " is rated by the users as easier to prepare than " + recipeB + \
                                  ", and this is adequate with your low cooking experience."
                elif(diffB < diffA):
                    explanation = recipeB + " is rated by the users as easier to prepare than " + recipeA + \
                                  ", and this is adequate with your low cooking experience."
                else:
                    explanation = recipeA + " is as easy to prepare as " + recipeB + "."
            
            if(user_skills == 3):
                if(diffA < diffB):
                    explanation = recipeA + " is rated by the users as easier to prepare than " + recipeB + \
                                  ", and this is adequate with your medium cooking experience."
                elif(diffB < diffA):
                    explanation = recipeB + " is rated by the users as easier to prepare than " + recipeA + \
                                  ", and this is adequate with your medium cooking experience."
                else:
                    explanation = recipeA + " is as easy to prepare as " + recipeB + "."

            if(user_skills == 4):
                if(diffA < diffB):
                    explanation = recipeA + " is rated by the users as easier to prepare than " + recipeB + \
                                  ", and this is adequate with your high cooking experience."
                elif(diffB < diffA):
                    explanation = recipeB + " is rated by the users as easier to prepare than " + recipeA + \
                                  ", and this is adequate with your high cooking experience."
                else:
                    explanation = recipeA + " is as easy to prepare as " + recipeB + "."

            if(user_skills == 5):
                if(diffA < diffB):
                    explanation = recipeA + " is rated by the users as easier to prepare than " + recipeB + \
                                  ", and this is adequate with your very high cooking experience."
                elif(diffB < diffA):
                    explanation = recipeB + " is rated by the users as easier to prepare than " + recipeA + \
                                  ", and this is adequate with your very high cooking experience."
                else:
                    explanation = recipeA + " is as easy to prepare as " + recipeB + "."

            return explanation

        """
        The explanation function foodFeatureHealthRisk_one compares recipe nutrients
        with the medical knowledge about the reference intake (RI). It outputs the
        small and great amount of nutrients and risks associated to a higher assumption
        of the specific nutrient.
        If the "great" nutrient is empty, there will be a risk associated to a higher
        assumption of the "small" nutrient.
        """
        def foodFeatureHealthRisk_one(recipeA, nutrients):
            explanation = ""
            small = ""
            great = ""
            risk = ""

            explanation,small,great = foodFeatures_one(recipeA, nutrients)

            if(great != ""):
                risk = random.choice(nutrients[great]["risks"])
                explanation += " Intake too much " + great + \
                               " can increase the risk of " + risk + "."
            else:
                risk = random.choice(nutrients[small]["risks"])
                explanation += " Intake too much " + small + \
                               " can increase the risk of " + risk + "."

            return explanation

        """
        The explanation function foodFeatureHealthRisk_two recall the function
        foodFeatures_two to make a comparison between the nutrients of the two recipes.
        The output is a string telling the risk associated to a higher assumption
        of the great/small nutrient.
        Then, there is a comparison of the amount of calories of the two recipes.
        """

        def foodFeatureHealthRisk_two(recipeA,recipeB,nutrients):

            explanation = ""
            small = ""
            great = ""
            risk = ""

            recipeA_name = recipeA["title"]
            recipeB_name = recipeB["title"]
            
            explanation,smallA,greatA = foodFeatures_two(recipeA, recipeB, nutrients)

            if(greatA != ""):
                risk = random.choice(nutrients[greatA]["risks"])
                explanation += " Intake too much " + greatA + \
                               " can increase the risk of " + risk + ". "
            
            
            if(recipeA["calories"]) > recipeB["calories"]:
                explanation += "Moreover, " + recipeA_name + " has more calories (" + str(recipeA["calories"]) + " gr) than " + recipeB_name + " (" + str(recipeB["calories"]) + " gr). "
                    
            elif(recipeB["calories"]) > recipeA["calories"]:
                explanation += "Moreover, " + recipeB_name + " has more calories (" + str(recipeB["calories"]) + " gr) than " + recipeA_name + " (" + str(recipeA["calories"]) + " gr). "
                    
            else:
                explanation += "Moreover, both recipes have got the same amount of calories (" + str(recipeA["calories"]) + " gr). "
                explanation += "Reference daily intake of calories is 1900 Kcal." 
                    

            return explanation

        """
        The explanation function foodFeatureHealthBenefits_one recall the foodFeatures_one
        and returns a string with the benefit of the small/great nutrient returned in the
        foodFeatures_one function.
        """

        def foodFeatureHealthBenefits_one(recipeA, nutrients):

            explanation = ""
            small = ""
            great = ""
            risk = ""

            explanation,small,great = foodFeatures_one(recipeA, nutrients)

            if(small != ""):
                benefit = random.choice(nutrients[small]["benefits"])
                explanation += " A correct daily intake of " + small + \
                               " can " + benefit + "."
            else:
                benefit = random.choice(nutrients[great]["benefits"])
                explanation += " A correct daily intake of " + great + \
                               " can " + benefit + "."

            return explanation

        """
        The explanation function foodFeatureHealthBenefits_two recall the
        foodFeatures_two function, and return a string with the benefit
        related to the small/great nutrient returned by the foodFeatures_two
        function.
        There is also a comparison between the calories of the two
        recommended recipes.
        """

        def foodFeatureHealthBenefits_two(recipeA,recipeB,nutrients):

            explanation = ""
            small = ""
            great = ""
            risk = ""

            recipeA_name = recipeA["title"]
            recipeB_name = recipeB["title"]
            
            explanation,smallA,greatA = foodFeatures_two(recipeA, recipeB, nutrients)

            if(smallA != ""):
                benefit = random.choice(nutrients[smallA]["benefits"])
                explanation += ". A correct daily intake of " + smallA + \
                               " can " + benefit + ". "
            
            if(recipeA["calories"]) > recipeB["calories"]:
                explanation += "Furthermore, " + recipeA_name + " has more calories (" + str(recipeA["calories"]) + " gr) than " + recipeB_name + " (" + str(recipeB["calories"]) + " gr). "
                    
            elif(recipeB["calories"]) > recipeA["calories"]:
                explanation += "Furthermore, " + recipeB_name + " has more calories (" + str(recipeB["calories"]) + " gr) than " + recipeA_name + " (" + str(recipeA["calories"]) + " gr). "
                    
            else:
                explanation += "Furthermore, both recipes have got the same amount of calories (" + str(recipeA["calories"]) + " gr). "
                explanation += "Reference daily intake of calories is 1900 Kcal."        

            return explanation

        """
        The explanation function foodFeatureHealthRisks_one recall the foodFeatures_one
        and returns a string with the risk of the small/great nutrient returned in the
        foodFeatures_one function.
        """

        def userFeatureHealthRisk_one(user, recipeA, nutrients):
                    
            explanation, small, great = foodFeatures_one(recipeA, nutrients)

            if(user["Mood"] == 'bad' or user["Mood"] == 'neutral' or user["Depressed"] == 'yes' or user["Stressed"] == 'yes'):
                listMood = ["sugars","carbohydrates","proteins"]
                if(great in listMood):
                    explanation += ". An excess of " + great + " can swing your mood"
                    
            if(user["BMI"] == "lower"):
                explanation += ", and may not be able to help you to gain weight."
            elif(user["BMI"] == "over"):
                explanation += ", and may not be able to help you to lose weight."
            
            return explanation

        """
        The explanation function foodFeatureHealthRisks_two recall the
        foodFeatures_two function, and return a string with the risk
        related to the small/great nutrient returned by the foodFeatures_two
        function.
        There is also a comparison between the calories of the two
        recommended recipes.
        """
        def userFeatureHealthRisk_two(user, recipeA, recipeB, nutrients):

            recipeA_name = recipeA["title"]
            recipeB_name = recipeB["title"]
            explanation, smallA, greatA = foodFeatures_two(recipeA, recipeB, nutrients)


            if(user["Mood"] == 'bad' or user["Mood"] == 'neutral' or user["Depressed"] == 'yes' or user["Stressed"] == 'yes'):
                listMood = ["sugars","carbohydrates","proteins"]
                if(smallA in listMood):
                    explanation += ". An excess of " + smallA + " can swing your mood"
             
            if(user["BMI"] == "lower" and greatA != ""):
                explanation += ", and may not be able to help you to gain weight. "
            elif(user["BMI"] == "over" and smallA != ""):
                explanation += ", and may not be able to help you to lose weight. "


            if(recipeA["calories"]) > recipeB["calories"]:
                explanation += "Also, " +recipeA_name + " has more calories (" + str(recipeA["calories"]) + " gr) than " + recipeB_name + " (" + str(recipeB["calories"]) + " gr)."
                    
            elif(recipeB["calories"]) > recipeA["calories"]:
                explanation += "Also, " +recipeB_name + " has more calories (" + str(recipeB["calories"]) + " gr) than " + recipeA_name + " (" + str(recipeA["calories"]) + " gr)."
                    
            else:
                explanation += "Also, both recipes have got the same amount of calories (" + str(recipeA["calories"]) + " gr). "
                explanation += "Reference daily intake of calories is 1900 Kcal." 		
            
            return explanation

        """
        The explanation function userFeatureHealthBenefits_one connects the recipe A with the user characteristics
        (BMI, mood, if he or she is depressed/stressed). It returns a string with a benefit related to a nutrient
        that is the output of the foodFeatures_one function.
        """
        def userFeatureHealthBenefits_one(user, recipeA, nutrients):
            
            explanation, small, great = foodFeatures_one(recipeA, nutrients)
            
            if(user["Mood"] == 'bad' or user["Mood"] == 'neutral' or user["Depressed"] == 'yes' or user["Stressed"] == 'yes'):
                listMood = ["sugars","carbohydrates","proteins"]
                if(small in listMood):
                    explanation += ". A correct intake of " + small + " can improve your mood"

            if(user["BMI"] == "lower"):
                if(great != ""):
                    explanation += ". A correct intake of " + great + " can help you to gain weight."
                else:
                    explanation += ". A correct intake of " + small + " can help you to gain weight."

            elif(user["BMI"] == "over"):
                explanation += ". A correct intake of " + small + " can help you to lose weight."


            return explanation


        """
        The explanation function userFeatureHealthBenefits_two recall the foodFeatures_two
        function, returns a string with a benefit related to the nutrient and the mood / BMI
        of the user.
        There is a comparison of the amount of calories of the two recommeded recipes.
        """
        def userFeatureHealthBenefits_two(user, recipeA, recipeB, nutrients):

            recipeA_name = recipeA["title"]
            recipeB_name = recipeB["title"]
            
            explanation, smallA, greatA = foodFeatures_two(recipeA, recipeB, nutrients)
            
            if(user["Mood"] == 'bad' or user["Mood"] == 'neutral' or user["Depressed"] == 'yes' or user["Stressed"] == 'yes'):
                listMood = ["sugar","carbohydrates","proteins"]
                if(smallA in listMood):
                    explanation += ". A correct intake of " + smallA + " can improve your mood"

            if(user["BMI"] == "lower" and greatA != ""):
                explanation += ". A correct intake of " + greatA + " can help you to gain weight. "
            elif(user["BMI"] == "over" and smallA != ""):
                explanation += ". A correct intake of " + smallA + " can help you to lose weight. "

            if(recipeA["calories"]) > recipeB["calories"]:
                explanation += "Moreover, " + recipeA_name + " has more calories (" + str(recipeA["calories"]) + " gr) than " + recipeB_name + " (" + str(recipeB["calories"]) + " gr). "
                    
            elif(recipeB["calories"]) > recipeA["calories"]:
                explanation += "Moreover, " +recipeB_name + " has more calories (" + str(recipeB["calories"]) + " gr) than " + recipeA_name + " (" + str(recipeA["calories"]) + " gr). "
                    
            else:
                explanation += "Moreover, both recipes have got the same amount of calories (" + str(recipeA["calories"]) + " gr). "
                explanation += "Reference daily intake of calories is 1900 Kcal." 

            return explanation

       
        #-----
        # Function get_string_exp returns the explanation of a specific type
        # (popularity, food features, user features, ecc...)
        #---

        def get_string_exp(user,
                           recipeA_values,
                           recipeB_values,
                           type_explanation,
                           listRestrictions,
                           nutrients):
            
            
            recipeA_name = recipeA_values['title']
            recipeB_name = recipeB_values['title']
           
           
            
            if type_explanation == 'popularity':
                expl = popularity(recipeA_name)
            elif type_explanation == 'foodGoals_one':
                user_goal = user['Goal']
                recipeA_calories = str(recipeA_values['calories'])
                expl = foodGoals_one(recipeA_name, user_goal, recipeA_calories)
            elif type_explanation == 'foodGoals_two':
                user_goal = user['Goal']
                recipeA_calories = recipeA_values['calories']
                recipeB_calories = recipeB_values['calories']
                            
                expl = foodGoals_two(user_goal,
                          recipeA_name,
                          recipeB_name,
                          recipeA_calories,
                          recipeB_calories)
            elif type_explanation == 'foodPreferences_one':
                #todo
                restriction = ""
                userRestrictions = user["User_restriction"]
                flag = 0
                random.shuffle(listRestrictions)
                i = 0
                #encoded(vegetarian,lactosefree,glutenfree,lownichel,light)
                while(flag == 0 and i < len(listRestrictions)):
                    if(listRestrictions[i] in userRestrictions):
                        restriction = listRestrictions[i]
                        description = restrictions["one"][restriction]
                        if(listRestrictions[i] == "lactosefree"):
                            restriction = "lactose-free"
                        if(listRestrictions[i] == "glutenfree"):
                            restriction = "gluten-free"
                        if(listRestrictions[i] == "lownichel"):
                            restriction = "low-nichel"
                        flag = 1
                        
                    i += 1
               
                expl = foodPreferences_one(restriction, description, recipeA_name)
                
            elif type_explanation == 'foodPreferences_two':
                #todo
                restriction = ""
                userRestrictions = user["User_restriction"]
                flag = 0
                random.shuffle(listRestrictions)
                i = 0
                
                while(flag == 0 and i < len(listRestrictions)):
                    if(listRestrictions[i] in userRestrictions):
                        restriction = listRestrictions[i]
                        description = restrictions["two"][restriction]
                        if(listRestrictions[i] == "lactosefree"):
                            restriction = "lactose-free"
                        if(listRestrictions[i] == "glutenfree"):
                            restriction = "gluten-free"
                        if(listRestrictions[i] == "lownichel"):
                            restriction = "low-nichel"
                        flag = 1
                        
                    i += 1
               
                expl = foodPreferences_two(restriction, description)

            elif type_explanation == 'foodFeatures_one':
                expl,_,_ = foodFeatures_one(recipeA_values,
                                            nutrients)
            elif type_explanation == 'foodFeatures_two':
                expl,_,_ = foodFeatures_two(recipeA_values,
                                            recipeB_values,
                                            nutrients)
                        
            elif type_explanation == 'userSkills_one':
                user_skills = int(user['Cooking_exp'])
                # molto facile, facile, media, difficile, molto difficile
                expl = userSkills_one(user_skills,
                                      recipeA_name)
                
            elif type_explanation == 'userSkills_two':
                user_skills = int(user['Cooking_exp'])
                diffA = recipeA_values['difficulty']
                diffB = recipeB_values['difficulty']
                expl = userSkills_two(user_skills,
                                      recipeA_name,
                                      recipeB_name,
                                      diffA,
                                      diffB)

            elif type_explanation == 'foodFeatureHealthRisk_one':
                expl = foodFeatureHealthRisk_one(recipeA_values, nutrients)

            elif type_explanation == 'foodFeatureHealthRisk_two':
                expl = foodFeatureHealthRisk_two(recipeA_values, recipeB_values, nutrients)

            elif type_explanation == 'foodFeatureHealthBenefits_one':
                        expl = foodFeatureHealthBenefits_one(recipeA_values, nutrients)

            elif type_explanation == 'foodFeatureHealthBenefits_two':
                        expl = foodFeatureHealthBenefits_two(recipeA_values, recipeB_values, nutrients)       

            elif type_explanation == 'userFeatureHealthRisk_one':
                        expl = userFeatureHealthRisk_one(user, recipeA_values, nutrients)

            elif type_explanation == 'userFeatureHealthRisk_two':
                expl = userFeatureHealthRisk_two(user, recipeA_values, recipeB_values, nutrients)

            elif type_explanation == 'userFeatureHealthBenefits_one':
                expl = userFeatureHealthBenefits_one(user, recipeA_values, nutrients)

            elif type_explanation == 'userFeatureHealthBenefits_two':
                expl = userFeatureHealthBenefits_two(user, recipeA_values, recipeB_values, nutrients)
            
            return expl

        #---

        PATH = 'Nutrient.json'
        restrictionsPath = 'Restrictions.json'
        print(request.args.get('imgurl1'))
    
        recipeA_url = request.args.get('imgurl1')
        recipeB_url = request.args.get('imgurl2')
        url_dataset_en = 'dataset_en.csv'


        #df = pd.read_csv(url_dataset_en)
		
        # read file
        with open(PATH, 'r') as myfile:
            data = myfile.read()

        with open(restrictionsPath, 'r') as myfile:
            dataRestrictions = myfile.read()
            
        nutrients = json.loads(data)
        restrictions = json.loads(dataRestrictions)
        listRestrictions = list(restrictions["one"].keys())
      
        df = pd.read_csv(url_dataset_en)

        recipeA_values = {}
        recipeB_values = {}

        for index, row in df.iterrows():
            if(row["imageURL"] == recipeA_url):
                recipeA_values = row
            if(row["imageURL"] == recipeB_url):
                recipeB_values = row

        recipeA_values["sodium"] = recipeB_values["sodium"]/1000
        recipeB_values["sodium"] = recipeB_values["sodium"]/1000
                    

        user = {
            'Mood'              : request.args.get('mood'), # bad/good/neutral
            'Stressed'          : request.args.get('stress'), # yes/no
            'Depressed'         : request.args.get('depression'),  # yes/no
            'BMI'               : request.args.get('bmi'), # over/lower/normal
            'Activity'          : request.args.get('activity'), #low/high/normal
            'Goal'              : request.args.get('goal'), #lose/gain/no
            'Sleep'             : request.args.get('sleep'), # low/good
            'User_restriction'  : request.args.get('restr'),
            #encoded(vegetarian,lactosefree,glutenfree,lownichel,light)
            'Prob'              : request.args.get('prob'),
            #encoded(heart,diabete,joint,pressure,chol)
            'Cooking_exp'       : request.args.get('difficulty'), # 1/2/3/4/5
            }


        
        expl_types = [  
                "popularity",
                "foodGoals_one",
                "foodGoals_two", 
                "foodPreferences_one",
                "foodPreferences_two",
                "foodFeatures_one", 
                "foodFeatures_two",
                "userSkills_one",
                "userSkills_two", 
                "foodFeatureHealthRisk_one",
                "foodFeatureHealthRisk_two",  
                "foodFeatureHealthBenefits_one", 
                "foodFeatureHealthBenefits_two", 
                "userFeatureHealthRisk_one",
                "userFeatureHealthRisk_two", 
                "userFeatureHealthBenefits_one",
                "userFeatureHealthBenefits_two"
                ]


        random.shuffle(expl_types)
        random_exp_name = expl_types[0:3]
        list_exp = []
        i = 0
        
        while(len(list_exp) < 3):
            expl = get_string_exp(user,
                           recipeA_values,
                           recipeB_values,
                           expl_types[i],
                           listRestrictions,
                           nutrients)
            if (expl != ""):
                list_exp.append(expl)
            i += 1
			
		#conversion Array to JSON
        json_exp = json.dumps({'explanation':list_exp})
		
		
        return json_exp
        
api.add_resource(Explain, '/exp/')

if __name__ == '__main__':
     app.run(port=5003)
