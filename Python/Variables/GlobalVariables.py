# Global variables can be used by everyone, both inside of functions and outside.

# If you create a variable with the same name inside a function, this variable will be local, and can only be used inside the function. The global variable with the same name will remain as it was, global and with the original value.

# When you create a variable inside a function, that variable is local and can only be usec inside that function. 
# To create a global variable inside a function, you can use the global keyword. 

    # example 
def testfunction ():
    global newVariable 
    newVariable = "This shit better work"

testfunction()

print(newVariable)

# To change the value of a global variable inside a function, refer to the variable by using the global keyword:
