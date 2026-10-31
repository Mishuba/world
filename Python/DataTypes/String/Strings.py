# You can assign a multiline string to a variable by using three quotes:

'''
strings in python are arrays of bytes representing unicode characters. 
However, Python does not have a character ata type, a single character is simply a string with a length of 1. 
Square brackets can be used to access elements of the string. 

    Example 
        a = "hello, world!"
        print(a[1])

Looping through a string 
    example 
        for x in "banana":
            print(x)

To get the length of a string use len() function 
    Example 
        a = "hello, World!"
        print(len(a))

To check if a certain phrase or character is present in a string, we can use the keyword in.
    Example 
        txt = "the best things in life are free!"
        print("free" in txt)

    Example 
        txt = "The best things in life are free!"
        if "free" in txt:
            print("Yes, 'free' is present.")

    Example
        txt = "The best things in life are free!"
        print("expensive" not in txt)

    Example 
        txt = "The best things in life are free!"
        if "expensive" not in txt:
            print("No, 'expensive' is NOT present.")


'''