package main;
import java.util.Scanner;

public class Converter {

	public static void main(String[] args) {	
		

			Scanner scanner = new Scanner(System.in);
			
			int menuSelection;
			int cups;
			int teaspoons;
			double miles;
			double kilometers;
			double UsGallons;
			double ImGallons;
			System.out.println("(1) Cups to Teaspoons \n2) Miles to Kilometers \n 3) US Gallons to Imperial Gallons \n 4) Quit"); 
			System.out.println("Selection: ");
			menuSelection = scanner.nextInt();
			
			while (menuSelection != 4) {
				
				switch (menuSelection) {			
				case 1:
					System.out.println("Enter the amount of cups to convert");
					cups = scanner.nextInt();
					teaspoons = cups * 48;
					System.out.println(cups +" cups = " + teaspoons + " teaspoons");
					break;
				
				case 2:
		            System.out.println("Enter the amount of miles to convert");
					miles = scanner.nextDouble();
					kilometers = miles * 1.609;
					System.out.println(miles +" miles = " + kilometers + " kilometers");
				break;
				
				case 3:
		            System.out.println("Enter the amount of US Gallons to convert");
					UsGallons = scanner.nextInt();
					ImGallons = UsGallons * 0.832674;
					System.out.println(UsGallons +" Imperial Gallons = " + ImGallons + " teaspoons");
				break;
				
				case 4:
					System.out.println("Good Bye");
				break;
				
				default:
					System.out.println("Invalid input");
					break;
					
					
			}
menuOption(0);
menuSelection = scanner.nextInt();
		
			}
		
		
		}	
	
	public static void MainMenu () {
		System.out.println("press a number to continue");
	}
	
	public static void menuOption(int i) {
			System.out.println("(1) Cups to Teaspoons \n2) Miles to Kilometers \n 3) US Gallons to Imperial Gallons \n 4) Quit"); 
			System.out.println("Selection: ");
		}
		
	}

	


	


		
		

		

		
	    /*

	    import java.util.Date;
	    import java.util.Scanner;

	    public class SwitchStatement {
	        public static void main(String() args) {
	            String dayOfTheWeek= "wednesday";

	            Scanner scanner = new Scanner(System.in);

	            // SwitchStatement
	            switch(dayOfTheWeek) {
	                case "Monday";
	                    System.outprintln("M");
	                    break;
	                case "Tuesday";
	                    System.out.println("T");
	                    break;
	                case "Wednesday";
	                    System.out.println("W");
	                    break;
	                case "Thursday";
	                    System.out.println("Th");
	                    break;
	                case "Friday";
	                    System.out.println("F");
	                    break;

	                default:
	                    System.out.println("Its the weekend")


	            }
	            double result = collectQuantity("pounds", "killos");
	            scan.close();
	        }

	        private static double collectQuantity(String until, String unit2) {
	            System.out.println("How much of "+unit1+" do you want to convert to "+unit2+"?");
	            return 20;

	        }
	    }

	    */

		
		

