# Recommended-Products

A simple recommended products module for a PrestaShop 8.

> It should be noted that this module could be optimized much better by adding an autocomplete using ajax or similar libs to the product search bar so that you don't have to search first before displaying the products. Due to the time and complexity of it, I have not been able to do it completely but with the help of a team and with time I could do it without problem. I hope you take this into account before evaluating my test. All the best.

## Facility

Follow these steps to proceed with the installation of a module in PrestaShop.

1. It is necessary to download the `.zip` file where it will contain the necessary files for its use. You can download it through this link.

2. Once you have the downloaded module file, access the **Module Manager** section, click on the _Upload a module_ button and drag or select your file. In a few moments your module will appear installed and you can easily edit it by clicking on _Configure_.
   ![A block of list modules](https://raw.githubusercontent.com/Natalio-R/Recommended-Products/main/images/img_01.png)
   From the **Module Manager** you can see all the available plugins classified by categories. On the right side of each of them you have the options to manage them as you wish.

## Setting

We continue with the configuration of the module in question.

1. At first glance you will see a list with all the products on the page. Select one or more products that you want to display in the recommended list.
   ![A first block of configuration](https://raw.githubusercontent.com/Natalio-R/Recommended-Products/main/images/img_02.png)
   Once selected, click the _Save_ button and you will see how they are added to the list.

2. A basic list of recommended products will be displayed below. The information displayed is the _Product ID_, _Product name_ and _Price with tax_, plus a simple button to _Remove the product_ from the list.
   ![A second block of configuration](https://raw.githubusercontent.com/Natalio-R/Recommended-Products/main/images/img_03.png)
   If the product is removed, it will no longer appear on the list in case we stop recommending it.

## How is it used?

1. First, once in the configuration section of the module, you will see a search bar next to a selectable list with all the products.

2. We can select the products we want directly from the list and hit the save button. Or, you can write the name of the product directly in the search bar so that when you click the Save button or the enter key, the list of products will be updated with the products related to the search term.

3. Finally, the selected products will be displayed in a list where you can see the name, price and ID of the product, along with a Delete button in case you want to delete the product from the list.

## How do we show the products in the Frontoffice?

As soon as the module is installed, it is automatically registered in the `displayHome` hook.

To move the position of the module within the hook, it is necessary to go to the positions section of PrestaShop, look for the `displayHome` hook and move the module to the position that you like best.

To finish, you only have to update the Frontoffice tab and you will be able to see the changes made.

## View in the Front-Office

Now if you go to the main page of the store, you can see the list of recommended products. A simple view where you can see the _image of the product_, _name_, _price_, button to _add to cart_ and another button to _add the product to the favorites list_.
![A block of front-office display](https://raw.githubusercontent.com/Natalio-R/Recommended-Products/main/images/img_04.png)

## Translations

A `.php` file with the module translations has been implemented. E. the section of Translations of the corresponding module will be able to modify the language.
![A block of tanslations](https://raw.githubusercontent.com/Natalio-R/Recommended-Products/main/images/img_05.png)

# Conclusion

This module has been created for a technical test for the company Netenders. Made in 3 days in the best possible way.
