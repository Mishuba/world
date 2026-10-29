"""              
    Access Items 
        List items are indexed and you can access them by referring to the index number:
            Example 
                thislist = ["apple", "banana", "cherry"]
                print(thislist[1])
                        
    Negative Indexing 
        Negative indexing means start from the end 
            -1 refers to the last item, -2 refers to the second last item etc. 
                Example 
                    thislist = ["apple", "banana", "cherry"]
                    print(thislist[-1]) 

    Range of Indexes 
        You can specify a range of indexes by specifying where to start and where to end the range. 
        When Specifying a range, the return value will be a new list with the specified items. 
            Example 
                thislist = ["apple", "banana", "cherry", "orange", "kiwi", "melon", "mango"]
                print(thislist[2:5])
        By leaving out the start value, the range will start at the first item: 
            Example 
                thislist = ["apple", "banana", "cherry", "orange", "kiwi", "melon", "mango"]
                print(thislist[:4])
        By leaving out the end value, the range will go on to the end of the list:
            Example 
                thislist = ["apple", "banana", "cherry", "orange", "kiwi", "melon", "mango"]
                print(thislist[2:])

    Range of Negative Indexes 
        Specify negative indexes if you want to start the search from the end of the list: 
            Example
                thislist = ["apple", "banana", "cherry", "orange", "kiwi", "melon", "mango"]
                print(thislist[-4:-1])

    Check if Item Exists 
        To determine if a specified item is present in a list use the in keyword:
            Exmaple 
                thislist = ["apple", "banana", "cherry"]
                if "apple" in thislist:
                    print("Yes, 'apple' is in the fruits list")
"""