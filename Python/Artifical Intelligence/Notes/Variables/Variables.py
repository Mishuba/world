#RULES FOR VARIABLE NAMES 
#   A variable name must start with a letter or underscore character 
#   A variable name cannot start with a number 
#   A variable name can only contain alpha-numeric characters and underscores (A-z, 0-9, and _)

# Python has no command for declaring a variable

#The best way to output multiple variables in the print() function is to separate them with commas, which even support different data types

x = 11

y = 18 

z = ("Just seeing if i can print a string with and without ()")

a = "yes you can either use () or not use () when developing strings in python"

print(x, z)

print(x+y)

print(x*y)

print(x-y)

print(z)

print(a)

# If you using a number and want to specify a data type do like the example im showing below. This is called casting.  This most likely will only be used for numbers. You can test it with other thing if you want.
#cASTING HERE

b = str(3)
c = int(3)
d = float(3)


#GETTING THE TYPE OF VARIABLE
""" use the type() function to gedt the data type of a variable """

print(type(a))

print(type(b))

print(type(c))

print(type(d))

print(type(x))

print(type(y))

print(type(z))


# Many Values to Multiple Variables 

e, f, g = ('Black', 'Red', 'Green')

print(e)
print(f)
print(g)

#ONE VALUE TO MULTIPLE VARIABLES 

h = i = j = k = ("2000 Mazda 626")

print(h)
print(i)
print(j)
print(k)

#UNPACK A COLLECTION 
fruits = ["grapes", "peaches", "grapefruit"]

l, m, n = fruits 

print(l)

print(m)

print(n)


