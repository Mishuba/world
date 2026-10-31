"""
Upper Case 
    upper()
        example
            a = "hello world"
            print(a.upper())

Lower Case 
    lower() 
        example 
            b = hello world"
            print(b.lower())

Remove Whitespace 
    strip()
        example
            c = " Hello World"
            print(a.strip())

Replace String
    replace()
        Example 
            d = hello, world"
            print(a.replace("H", "j"))

Split String 
    split()
        example 
            e = "Hello World"
            print(a.split(","))

String Concatenation 
    To concatenate, or combine, two strings you can use the + operator.
        Example 
            f = "hello"
            g = "world"
            h = f + g
            print(h)

        Example
            i = f + " " + g

String Format 
    we can combine strings and numbers by using the format() method!
    The format() method takes the passed arguments, formats them, and places them in the string where the placeholders {} are:
        Example 
            age = 40
            txt = 'My name is John, and I am {}'
            print(txt.format(age))

    you can use index numbers {0} to be sure the arguments are placed in the correct placeholders 
        Example 
            quantity = 3
            itemno = 567 
            price = 49.95 
            myorder = "I want to pay {} dollars for {} pieces of item {}."
            print(myorder.format(quantity, itemno, price))


    you can use index numbers {0} to be sure the arguments are placed in the correct placeholders 
        Example 
            quantity = 3
            itemno = 567 
            price = 49.95 
            myorder = "I want to pay {2} dollars for {0} pieces of item {1}."
            print(myorder.format(quantity, itemno, price))

Escape Characters 
    YOu will get an error if you use double quotes inside a string that is surrounded by double quotes
    To fix this problem use the escape character \":
        Example 
            txt = "We are the so-called \"Vikings\" from the north."

escape Characters List 
\'      Single Quote
\\      Backslash
\n      New Line
\r      Carriage Return 
\t      Tab 
\b      Backspace
\f      Form Feed
\ooo    Octal Value 
\xhh    Hex Value

"""