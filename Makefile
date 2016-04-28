deb:
	rm -rf workermand.phar
	php -f build.php
	chmod +x workermand.phar
	rm -rf fakeroot
	mkdir -p fakeroot/usr/bin/
	cp workermand.phar fakeroot/usr/bin/
	mkdir -p fakeroot/etc
	cp workermand.ini fakeroot/etc
	mkdir -p fakeroot/etc/init.d
	cp bin/init.d.workermand fakeroot/etc/init.d/workermand
	chmod +x fakeroot/etc/init.d/workermand
	cp -r DEBIAN fakeroot
	dpkg -b fakeroot workermand.deb

clean:
	rm -rf workermand.phar
	rm -rf fakeroot
