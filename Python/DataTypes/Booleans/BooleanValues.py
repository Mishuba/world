"""
When you compare two values, the expression is evaluated and Python returns the Boolean answer:
    Example 
        print(10 > 9)
        print(10 = 9)
        print(10 < 9)

When you run a condition in an if statement, Python returns True or False

The bool() function allows you to evaluate any value, and give you True or False in return
    Example 
        print(bool("Hello"))

Almost any value is evaluated to True if it has some sort of content.
Any string is True, except empty strings.
Any number is True, except 0.
Any list, tuple, set, and dictionary are True, except empty ones.

In fact, there are not many values that evaluate to False, except empty values, such as (), [], {}, "", the number 0, and the value None. And of course the value False evaluates to False

One more value, or object in this case, evaluates to False, and that is if you have an object that is made from a class with a __len__ function that returns 0 or False

"""