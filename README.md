# MUtil #

MUtil is a collection of PHP library classes that extend the Zend Framework for web developement.

The collection contains a number of simple utility classes as well as containing an alternative framework for creating Html using the MVC extentions described next.

## Model ##

The Model classes implement not just storage models (reading and writing to files and databases) but also meta data about the data like labels, constraints and value options.

This meta information allows generic code to be used for standard views for browsing, viewing, editing and deleting.

## Lazy / Late execution ##

The Lazy classes allow "late execution". Late execution means the code is not executed where it is specified in the code, but at a later moment, usually the moment when the output of the page is rendered.

This allows a programmer to state e.g. that the colspan attribute of a cell object in a table object should have the value of the highest number of columns of each of the table rows. 
If extra columns are added to the table at a later stage (but before rendering) then the colspan value will still be correct.

The most common use is in combination with repetition. Instead of looping through table data and adding a row each time, we just create a row and a repeater object. 
The row is the filled with objects that will read the data from the repeater object at rendering time. When rendering the table the row is rendered again and again, as long as the repeater object has row data. 
The output is the same as we would have had we looped through the data, but the code looks more elegant (in my opinion) and usually creates less objects (if the output contains more than two rows).

## Html ##

The html classes allow you to create HTML using object oriented notation, using an array-like object. 
String elements are attributes (except when a different interpretation makes more sense), numeric items fill the content. 
You create sub-elements throug functions calls. If you have an html object and want to create a DIV element with the class attribute 'Hhllo' and World as the content you just call:

    $html->div(array('class' => 'hello'), 'World');

The Html classes understand late execution.

## Snippets ##

Snippets are html objects with routing added. E.g. a Form snippet usually displays an Html form, but if the form data is saved the snippet redirect the output e.g. to a detailed view.

Snippets combine the Model and the Html classes. There are standard snippets to create forms for editing, detail view pages and paginated browse pages; all using the meta data in the model. 
(Of course the browse pages use the Lazy classes for creation.)

[![Build Status](https://travis-ci.org/GemsTracker/MUtil.svg?branch=master)](https://travis-ci.org/GemsTracker/MUtil)
