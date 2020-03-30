#!/usr/bin/env python
# source: http://www.unicode.org/reports/tr39


import urllib.request
import json

url = 'http://data.iana.org/TLD/tlds-alpha-by-domain.txt'
destFile = 'tlds.txt'
jsonFile = 'tlds.json'

urllib.request.urlretrieve(url, destFile)

f = open(destFile, 'r')
allLines = f.readlines()
f.close()

result = ""
for line in allLines:
	line = line.rstrip('\n')
	if (len(line) != 0) and (line[0] != '#') and (not line.startswith('XN--')):
		result = result + '"' + line.lower() +'": null, '
result = result.rstrip(', ')
result = '{"tlds": {'+result+'}}'
print(result)

f = open(jsonFile, 'w')
f.write(result)
f.close()
