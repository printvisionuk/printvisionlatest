#Magenest Barclaycard ePDQ payment gateway
Thank you for buying our product.
This extension address to [Magenest](https://store.magenest.com/).

## User guide
- If you have trouble installing this extension, please visit: http://www.confluence.izysync.com/display/DOC/1.+Barclaycard+ePDQ+Payment+Gateway+Installation+Guide

- For detailed user guide of this extension, please visit: http://www.confluence.izysync.com/display/DOC/2.+Barclaycard+ePDQ+Payment+Gateway+User+Guide

- Support portal: http://servicedesk.izysync.com/servicedesk/customer/portal/57

- All the updates of this module are included in CHANGELOG.md file.
##Install extension
###Go to the magento root folder, run these command to install.
```
bin/magento setup:upgrade
```
##Config the extension
### In barclaycard ePDQ go Configuration -> Technical Information -> Transaction feedback
```
Checkbox : I would like to receive transaction feedback parameters on the redirection URLs.
AcceptUrl : (Your Store website)/barclaycard/checkout/accept
DeclineUrl : (Your Store website)/barclaycard/checkout/decline
CancelUrl : (Your Store website)/barclaycard/checkout/cancel
ExceptionUrl : (Your Store website)/barclaycard/checkout/exception
```
### In barclaycard ePDQ go Configuration -> Technical Information -> Data & Origin verification
```
URL of the merchant page containing the payment form that will call the page: orderstandard.asp
Input your store Url
```
###After installed, go to admin page:
#####Stores -> Configuration -> Sales -> Payment Methods -> Barclaycard ePDQ
```
Follow the set up instrument .
```
##Test sandbox
https://mdepayments.epdq.co.uk/Ncol/Test/BackOffice/Home/index
