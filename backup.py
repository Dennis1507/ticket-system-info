import os
import os
import shutil

source_folder = os.getcwd()
destination_folder = os.path.join(os.path.join(os.environ['USERPROFILE']), 'Desktop') + '\\phpbackup\\'

if not os.path.isdir(destination_folder):
    os.mkdir(destination_folder)

for file in os.listdir(source_folder):

    source = source_folder + '\\' + file
    destination = destination_folder + file

    if os.path.isfile(source):
        shutil.copy(source, destination)
        print("Copied file: " + file)