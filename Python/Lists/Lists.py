"""

List are used to store mulitple items in a single variable 

    Example
        thislist = ["apple", "banana", "cherry"]
        print(thislist)


    List items are orered, changeable, and allow duplicate values.
    List items are indexed, the first item has index [0], the second item has index [1] etc. 

    Ordered
        When we say that lists are orderd, it means that the items have a defined order, and that order will not change. 
        If you add new items to a list, the new items will be placed at the end of the list.

    Changeable 
        Thelist is changeable, meaning that we can change, add, and remove items in a list after it has been created. 

    Allow Duplicates 
        Since lists are indexed, lists can have items with the same value:

    List Length
        To deteremine how many items a list has, use the len() function: 

    List Items - Data Types 
        List items can be of any data type:
            Example
                list1 = ["abc", 34, True, 40, "male"]

        type()
            From Python's perspective, lists are definedas objects with the data type 'list':
                <class 'list'> 

    The list() Constructor 
        It is also possible to use the list() constructor when creating a new list. 
            Example
                thislist = list(("apple", "banana", "cherry")) # note the double round-brackets 
                print(thislist)
"""