#!/usr/bin/env python
# source: http://www.unicode.org/reports/tr39


import urllib.request
import json

url = 'https://unicode.org/Public/security/latest/confusables.txt'
destFile = 'confusables.txt'
jsonFile = 'confusables.json'

urllib.request.urlretrieve(url, destFile)

f = open(destFile, 'r')
allLines = f.readlines()
f.close()

result = ""
for line in allLines:
	line = line.rstrip('\n')
	line = line.replace('\t', '')
	if (len(line) != 0) and (line[0] != '#') and (line[0] != '\ufeff'):
		line = line.split(';')
		line[0] = line[0].replace(' ', '').lower()
		tmp = line[1].rstrip(' ').split(' ')
		for item in tmp:
			result = result + '"' + line[0] +'": "' + item.lower() + '", '
result = result.rstrip(', ')
result = '{"confusables": {'+result+'}}'

f = open(jsonFile, 'w')
f.write(result)
f.close()
