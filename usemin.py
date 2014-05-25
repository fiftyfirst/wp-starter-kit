import os
import subprocess
import sys
import re


node_bin = os.getcwd() + '/node_modules/.bin/'
input_file = sys.argv[1]
output_file = sys.argv[2]
prefix = sys.argv[3]
version = sys.argv[4]
current_assets = []
current_target = None
all_assets = []
record = None
line = 0
output = ''

for i in ['cleancss', 'uglifyjs']:
    try:
        subprocess.check_output('which ' + node_bin + i, shell=True)
    except:
        print 'Error: Binary "' + i + '" was not found in ' + node_bin
        sys.exit()

if len(sys.argv) < 5:
    print 'Usage:'
    print (
        'python usemin.py [SOURCE FILE] [OUTPUT FILE] [WORK DIRECTORY] ' +
        '[VERSION]'
    )
    sys.exit()

with open(input_file) as f:
    print (
        u'\033[36m\u29d7\033[0m Searching input file "' + input_file +
        '" for assets...'
    )
    for i, l in enumerate(f.readlines()):
        begin = re.search('<!-- build:(css|js) (.*?) -->', l)
        end = re.search('<!-- endbuild -->', l)
        if record and end:
            all_assets.append({
                'type': record,
                'dest': re.sub('^.*?\/', '', current_target),
                'source': current_assets
            })
            if record == 'js':
                output += '<script src="' + current_target + '"></script>\n'
            elif record == 'css':
                output += (
                    '<link rel="stylesheet" href="' + current_target + '">\n'
                )
            current_assets = []
            current_target = None
            record = None
            line = i
        if not record and begin:
            record = begin.group(1)
            current_target = re.sub(
                '\.(css|js)$', '-' + version + r'.\1', begin.group(2)
            )
            line = i
        if i > line or i == 0:
            if record == 'js':
                current_assets.append(
                    re.search('src=".*?\/(.*?)"', l).group(1)
                )
            elif record == 'css':
                current_assets.append(
                    re.search('href=".*?\/(.*?)"', l).group(1)
                )
            if not record:
                output += l


print u'\033[33m\u279c\033[0m Writing output to file "' + output_file + '"...'
with open(output_file, 'w') as f:
    f.write(output)

for a in all_assets:
    if a['type'] == 'js':
        print (
            u'\033[33m\u279c\033[0m Minifying and concatenating JavaScript ' +
            'assets to file "' + prefix + a['dest'] + '"...'
        )
        for s in a['source']:
            subprocess.call(
                node_bin + 'uglifyjs ' + prefix + s + ' >> ' + prefix +
                a['dest'],
                shell=True
            )
    elif a['type'] == 'css':
        print (
            u'\033[33m\u279c\033[0m Minifying and concatenating CSS assets ' +
            'to file "' + prefix + a['dest'] + '"...'
        )
        for s in a['source']:
            subprocess.call(
                node_bin + 'cleancss -o ' + prefix + a['dest'] + ' ' + prefix +
                s,
                shell=True
            )

print u'\033[32m\u2714\033[0m All done!'
