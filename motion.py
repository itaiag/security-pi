#!/usr/bin/python
import StringIO
import subprocess
import os
import time
import smtplib
from datetime import datetime
from PIL import Image
from email.MIMEMultipart import MIMEMultipart
from email.MIMEBase import MIMEBase
from email.MIMEText import MIMEText
from email.Utils import COMMASPACE, formatdate
from email import Encoders
from threading import Thread
#from threadPool import ThreadPool


# Motion detection settings:
# Threshold (how much a pixel has to change by to be marked as "changed")
# Sensitivity (how many changed pixels before capturing an image)
# ForceCapture (whether to force an image to be captured every forceCaptureTime seconds)
threshold = 10
sensitivity = 20
forceCapture = True
forceCaptureTime = 60 * 60 # Once an hour

# File settings
saveWidth = 1280
saveHeight = 960
diskSpaceToReserve = 1000 * 1024 * 1024 # Keep 1000 mb free on disk

# Capture a small test image (for motion detection)
def captureTestImage():
    command = "raspistill -w %s -h %s -t 0 -e bmp -o -" % (100, 75)
    imageData = StringIO.StringIO()
    imageData.write(subprocess.check_output(command, shell=True))
    imageData.seek(0)
    im = Image.open(imageData)
    buffer = im.load()
    imageData.close()
    return im, buffer

# Save a full size image to disk
def saveImage(width, height, diskSpaceToReserve):
    keepDiskSpaceFree(diskSpaceToReserve)
    if not os.path.exists("images/"):
	    os.makedirs("images/")
	    os.chmod("images/",0777)
    time = datetime.now()
    filename = "images/capture-%04d%02d%02d-%02d%02d%02d.jpg" % (time.year, time.month, time.day, time.hour, time.minute, time.second)
    subprocess.call("raspistill -w 1296 -h 972 -t 0 -e jpg -q 15 -o %s" % filename, shell=True)
    os.chmod(filename,0777)
    print "Captured %s" % filename
    return filename

# Keep free space above given level
def keepDiskSpaceFree(bytesToReserve):
    if (getFreeSpace() < bytesToReserve):
        for filename in sorted(os.listdir(".")):
            if filename.startswith("capture") and filename.endswith(".jpg"):
                os.remove(filename)
                print "Deleted %s to avoid filling disk" % filename
                if (getFreeSpace() > bytesToReserve):
                    return

# Get available disk space
def getFreeSpace():
    st = os.statvfs(".")
    du = st.f_bavail * st.f_frsize
    return du

def sendGmail(attachment):
    fromaddr = 'itai.agmon@gmail.com'
    toaddrs  = ['itai.agmon@gmail.com']
    subject="Motion was detected "

    # Credentials (if needed)
    username = 'itai.agmon'
    password = ''

    # The actual mail send
    server = smtplib.SMTP('smtp.gmail.com:587')
    server.starttls()
    server.login(username,password)
    msg = MIMEMultipart()
    msg['From'] = fromaddr
    msg['To'] = COMMASPACE.join(toaddrs)
    msg['Date'] = formatdate(localtime=True)
    msg['Subject'] = subject

    msg.attach( MIMEText("Motion was detected!") )
    part = MIMEBase('application', "octet-stream")
    part.set_payload( open(attachment,"rb").read() )
    Encoders.encode_base64(part)
    part.add_header('Content-Disposition', 'attachment; filename="%s"' % os.path.basename(attachment))
    msg.attach(part)
    server.sendmail(fromaddr, toaddrs, msg.as_string())
    server.quit()

        
# Get first image
image1, buffer1 = captureTestImage()

# Reset last capture time
lastCapture = time.time()
print "Started motion detection"
while (True):

    # Get comparison image
    image2, buffer2 = captureTestImage()

    # Count changed pixels
    changedPixels = 0
    #pool = ThreadPool(20)
    for x in xrange(0, 100):
        for y in xrange(0, 75):
            # Just check green channel as it's the highest quality channel
            pixdiff = abs(buffer1[x,y][1] - buffer2[x,y][1])
            if pixdiff > threshold:
                changedPixels += 1

    # Check force capture
    if forceCapture:
        if time.time() - lastCapture > forceCaptureTime:
            changedPixels = sensitivity + 1
                
    # Save an image if pixels changed
    if changedPixels > sensitivity:
        lastCapture = time.time()
        fileName = saveImage(saveWidth, saveHeight, diskSpaceToReserve)
        #pool.add_task(sendGmail,fileName)
        
	#Ucomment this for sending mails
	#thread = Thread(target = sendGmail, args = (fileName, ))
        #thread.start()
    
    # Swap comparison buffers
    image1 = image2
    buffer1 = buffer2
