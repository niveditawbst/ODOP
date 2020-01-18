__deployNow() {


    echo "### Deploying now"


    git pull origin master






 rm -rf var/di/* var/generation/* var/cache/* var/log/* var/page_cache/* var/session/* var/view_preprocessed/* pub/static/*
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy -f  en_US
php bin/magento cache:clean
php bin/magento cache:flush
php bin/magento indexer:reindex






    chmod -R 777 var generated pub/static pub/media

    echo "*************************"
    echo "     Deployment done"
    echo "*************************"
}


__deployNow