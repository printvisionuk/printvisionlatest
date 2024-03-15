##Product Inquiry with Hide Price and Add To Cart Button Extension

##Support: 
version - 2.3.x, 2.4.x

##How to install Extension

1. Download the archive file. 
2. Unzip the files 
3. Create a folder path [Magento_Root]/app/code/Printvision/ProductInquiry 
4. Drop/move the unzipped files to directory '[Magento_Root]/app/code/Printvision/ProductInquiry'

#Enable Extension:
- php bin/magento module:enable Printvision_ProductInquiry
- php bin/magento setup:upgrade
- php bin/magento setup:di:compile
- php bin/magento setup:static-content:deploy
- php bin/magento cache:flush

#Disable Extension:
- php bin/magento module:disable Printvision_ProductInquiry
- php bin/magento setup:upgrade
- php bin/magento setup:di:compile
- php bin/magento setup:static-content:deploy
- php bin/magento cache:flush
