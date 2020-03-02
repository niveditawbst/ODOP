

__deployNow() {


    echo "### Deploying now"


    git pull origin master


    #~ echo "### composer install ..." &&
    #~ composer install



sudo rm -rf var/di/* var/generation/* var/cache/* var/log/* var/page_cache/* var/session/* var/view_preprocessed/* pub/static/*
sudo php bin/magento setup:upgrade
sudo php bin/magento setup:di:compile
sudo php bin/magento setup:static-content:deploy -f en_US
sudo php bin/magento cache:clean
sudo php bin/magento cache:flush
sudo php bin/magento indexer:reindex






    sudo chmod -R 777 var generated pub/static pub/media

    echo "*************************"
    echo "     Deployment done"
    echo "*************************"
}


__deployNow


