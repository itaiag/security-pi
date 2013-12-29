security-pi
===========

Motion detection for the camera module in the Raspberry PI computer. 
Includes a very simple web application.

Web application features:
* Start/stop motion detection.
* Browse images. 
* Clean all captured images.
* Send email with the captured images.
* Single image snapshot.


Installation
-----------------

* Since the project needs a web server, start by installing lighttpd and PHP5. You would also need the GD module for the jpg handling:


```
sudo apt-get install lighttpd
```

```
sudo apt-get install php5
```

```
sudo apt-get install php5-gd
```


* Since the web server user, www-data does not have access to the rasbistill which is required for capturing images. To grant access to the user type:

```
echo 'SUBSYSTEM=="vchiq",GROUP="video",MODE="0660"' > /etc/udev/rules.d/10-vchiq-permissions.rules
    usermod -a -G video www-data
```

* Copy all the project files to /var/www/security-pi (or clone it to this folder directly).
* The web server user would also need permissions to write the image files to the disk. To grant it access to the folder type:

```
sudo chown -R www-data:www-data /var/www/security-pi
```

* Browse to http://[your ip]/security-pi
