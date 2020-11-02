#!/usr/bin/env python3
# -*- coding: utf-8 -*-

from bs4 import BeautifulSoup
import re
import requests
import csv
import time


path = '/var/www/html/php/TvScripts'
direct = path
day = time.strftime('%d-%m-%Y') 
filepath = direct+'/'+day+'.csv'

file_prova = 'mondo'



data = {}
data['canale'] = []

programmiTV = []
canali = []




req = requests.get('http://www.programmitv.it/stasera.html')

guida = req.text
#guida = open('guida3.html' , encoding = 'utf-8');
soup = BeautifulSoup(guida, 'html.parser' ,from_encoding='utf-8')


#print(soup.prettify())

#ciclo per i canali
temp = soup.find_all(href = True , title = True , class_ = 'giallo')
contatore1=0
for tv in temp[10:]:
    nomeCanale= tv.get('title')
    print(nomeCanale)
    canali.insert(contatore1,nomeCanale)
    contatore1 = contatore1+1
        

dizionario = {}
temp2 = soup.find_all('pre')
contatore2=0;
for tv1 in temp2:
    
    programmazione = tv1.get_text()
#    print("La programmazione:"+programmazione)
    #programmiTV ha dentro di se le<pre> intere.
    programmiTV.insert(contatore2,programmazione)
#    print("Programmi tv:"+programmiTV)
    dizionario[canali[contatore2]] = programmiTV[contatore2]
#    print(programmiTV) #ad ogni iterazione abbiamo un canale
    contatore2=contatore2+1
    

print('TEST 1')
    
#print(canali[0])
#print(programmiTV[0])

#print(dizionario)
listaOrari = []
listaTesti = []
orarioRegex = re.compile(r' .\d\.\d\d+')
#print(orarioRegex.match(" 1.00"))
#print(orarioRegex.match(" /23.10"))

orarioFine1CifraRegex = re.compile(r'./\d\.\d\d')
#print(orarioFine1CifraRegex.match("pinopinoo /1.00"))

orarioFine2CifreRegex = re.compile(' /\d\d\.\d\d')
#print(orarioFine2CifreRegex.match(" /23.10"))

listaTitoli = []
lista = {}
   
            

print('TEST 2')



lunghezzaProgrammiTV = range (len(programmiTV))

for k in lunghezzaProgrammiTV:
    loc = programmiTV[k]
    loc1 = loc.replace("\n", " ")
    loc2 = loc1.replace("         ", " ")
    programmiTV[k] = loc2
#    print("Normalmente era:" +loc)
#    print("Adesso e: "+loc2)



print('TEST 2.5')

#print(len(programmiTV))
#Adesso i dati sono ordinati tutti su una riga, provo a processarli così

try:
  
    listaOra = []
    listaResi = []
    programmaSingolo=""
    print(lunghezzaProgrammiTV)
    for n in lunghezzaProgrammiTV:

        programmaSingolo = programmiTV[n]
        #print(programmiTV[n])
        #print(programmaSingolo)
        
        if re.search(r' .\d\.\d\d' , programmaSingolo)  != None:
            zio = re.findall(r' .\d\.\d\d' , programmaSingolo)
            print(zio)
            len(zio)
            listaOra.append(zio)
            na = range(len(zio))
            for pk in na:

    #            print ("La riga e: "+str(listaOra[n][pk]))

                if '/' in listaOra[n][pk]:
                    print("pre del" +   str(len(listaOra[n]))     ) 
                    del(listaOra[n][pk])
                    print("Post del" +       str(len(listaOra[n]))    )

                       
            dipo = re.search(r' .\d\.\d\d' , programmaSingolo) 
            ora = dipo.group()
    #        print(ora)
            resi = re.split(r' .\d\.\d\d', programmaSingolo)
            local = resi[1:]
            #print(local)
    #        print(resi)
    #        print(local)
    #        print(len(resi))
            mm = range(len(resi)-1)
    #        print(mm)
    #        print(len(resi))
            for z in mm:
                
                if resi[z] == '' or resi[z] == ' ' or resi[z]=='\r ':
                    del(resi[z])
                    continue
                
            #ora elimino le cose vuote pure da resi
            
            listaResi.append(resi)
        
except Exception as err:
    print(err)
        

    
print('TEST 3')   


cicloEsterno = range(len(programmiTV))
    
titleRegex = re.compile(r'.\(')
#strs = "  Big Game - Caccia al Presidente (FILM) info Thriller-avventura 2014, con Samuel L. Jackson "
#gruppo = re.search(r'.\(' , strs)
#print(gruppo.group())

#quello che c'è nelle parentesi, cioè il tipo
#tipologia = re.search(r'.\(.+\)' , strs)
#print(tipologia.group())

#attenzione perchè ci sta ,con ,conduce ,speciale(non gestirlo)
#personaggi = re.split(r',.conduce|con' , strs)
#print(personaggi[1])

#caratteristiche programma
#caratteristiche = re.search('\\).+,' , strs)
#print(caratteristiche.group())

listaConduttori = []
listaTipologie = []
#print (titleRegex.match(strs))
listaTitoli = []
titolo = ""
dictfinal = {}

print("MESSAGGIO DI PROVA DOPO IL FATTO")


"""
try:

    f = open('guida_tv.csv',mode='w+', newline='', encoding='utf-8-sig')
    s = csv.writer(f, delimiter=';')
    s.writerow(['A'] + ['B'])

    f.flush()
    f.close()
    
except IOError as err:
    
    print(err)
"""
try:


    #ad open dovrò passare filepath
    csv_file=open(filepath, mode='w+', newline = '' , encoding ='utf-8-sig')
        
    print("dentro")
    fieldnames = ['NomeCanale' , 'Titolo','Tipo','Genere' , 'Attori' , 'orario']

    scrittore = csv.writer(csv_file, delimiter = ';')
    scrittore.writerow(['NomeCanale'] + ['Titolo'] + ['Tipo'] + ['Genere'] + ['Attori'] + ['orario'])

    #tv_writer = csv.DictWriter(csv_file, fieldnames=fieldnames , delimiter=';')
    #csv.DictWriter()
    
    #tv_writer.writeheader()

    
    
    for righe in cicloEsterno:
        
         #canali  
         chan = canali[righe]
         print(chan)
         dictfinal['NomeCanale'] = chan
         canne = chan
     #    print(canali[righe])    
         print(righe)
         cicloInterno = range(len(listaOra[righe] ))
        
        
        
         for colonne in cicloInterno:
            
             ora = listaOra[righe][colonne].strip()
             dictfinal['orario'] = ora
             hr = ora
             print(listaOra[righe][colonne].strip())
             #arriva fino ad ora
            
     #        print("outer")
            
    
                 #se trovi la parentesi per il titolo, lo isoli
             if re.search(r'.\(',listaResi[righe][colonne])!= None:
     #            print("inner")
                 print('dentro riga listaresi')
                 titolo = re.split(r'.\(', listaResi[righe][colonne])
                # print(titolo[0].strip())
                 dictfinal['Titolo'] = titolo[0].strip()
                 tit = titolo[0].strip()
                 listaTitoli.append(titolo[0].strip())
                 print('if del titolo')
              #se non c'è la parentesi, sarà un tg, ipotizzalo e prendi la riga
             else:
     #            print("innter else")
                 titolo = listaResi[righe][colonne]
                 #print(titolo.strip())
                 dictfinal['Titolo'] = titolo.strip()
                 tit = titolo.strip()
                 listaTitoli.append(titolo.strip())
                 print('else del titolo')
            
              #fine gestione titoli
              #inizio gestione generi

             if re.search(r'.\(.+\)' , listaResi[righe][colonne]) != None:
                 print('print del tipo')
                 tipologia = re.search(r'.\(.+\)' , listaResi[righe][colonne])
                 tipo = tipologia.group().strip()
                 #print(tipo)   NON CI CREDO CHE ERI TU IL PROBLEMA, NON HA UN CAZZO DI SENSO.
                 dictfinal['Tipo'] = tipo
                 tip = tipo
            
             #caratteristiche programma
             if re.search( '\\).+,' ,listaResi[righe][colonne] ) != None:
                 print('entriamo nelle car')
                 caratteristiche = re.search('\\).+,' , listaResi[righe][colonne])
                 print('1')
                 if ') info' in caratteristiche.group():
                     bo = caratteristiche.group().replace(") info" , "").strip().rstrip(",")
                     print('2')
                     if '\r' in bo:
                         bo = bo.replace('\r' , '')
                       
                     if '\n' in bo:
                         bo = bo.replace('\n' , '')
                       
                     #print(bo)
                     dictfinal['Genere'] = bo
                     cars = bo
                     print('3')
                 elif ')' in caratteristiche.group():
                     bo = caratteristiche.group().replace(")" , "").strip().rstrip(",")
                     print('4')
                     #può capitare una i di errore nel sito, che abbrevia info, proviamo a gestirla
                     if '\r' in bo:
                         bo = bo.replace('\r' , '')
                        
                     if '\n' in bo:
                         bo = bo.replace('\n' , '')
                     print('5')
                     dictfinal['Genere'] = bo
                     cars = bo
                    #print(bo)
             else:
                 print('6')
                 dictfinal['Genere'] = 'TVMeteo'
                 cars = 'TVMeteo'
                 #print("TG/METEO")
               
               
             if re.search(r',.conduce|con ',listaResi[righe][colonne] )!= None:
                 print('7')
               
                 personaggi = re.split(r',.conduce|con ' , listaResi[righe][colonne])
                 personaggi = personaggi[1].strip()
               
                 #orario finale è sempre nella forma /dd.dd
                 if re.search(r' /.\d\.\d\d', personaggi) != None:
     #                print("siamo dentro")
                     personaggii = re.split(r'/.\d\.\d\d',personaggi)
                     #print(personaggii[0])
                     pers = personaggii[0]
                     dictfinal['Attori'] = pers
                     perdenti = pers
                 else:
                     #print(personaggi)
                     dictfinal['Attori'] = personaggi
                     perdenti = personaggi

             else:
                        
                print('non conduce')
                perdenti =""        
                   
 #        tv_writer.writerow()   
         #qua andrà scritta la riga, lo stacco tra un prog e un altro            
 #        print('\n')
             #tv_writer.writerow(dictfinal)
             scrittore.writerow([canne]+[tit]+[tip]+[cars]+[perdenti]+[hr])
             dictfinal['Attori'] =""
             perdenti = ""
                   
     #        print(listaOra[righe][colonne] )    
     #        print( listaResi[righe][colonne] ) 
        
    
    csv_file.flush()
    csv_file.close()    

except IOError as orrore:
    print("errore")
    #print(orrore)    
           
      
    
    

        







