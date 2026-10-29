package game;
import java.util.Scanner; 
import fixtures.Room;

public class Main {
	
	static Scanner scanner = new Scanner(System.in);
	
	
	
	
	public static void main(String[] args) {
		
		Player player = new Player();
		RoomManager rm = new RoomManager();
		rm.init();
		
		Room currentRoom = rm.startingRoom; // player.currentRoom = rm.starting
		String input = " ";
		while (!input.equals("quit")) {
			System.out.println("The " + currentRoom.getName() + " is your current location \n" );
			System.out.println("which direction would you like to go:"
					+ "forward \n"
					+ " backward \n"
					+ " right\n"
					+ " left\n"
					+ " up\n"
					+ " down"
					+ "\n Please type in your answer."
					+ "\n type 'quit' to finish");
			input = scanner.nextLine();
			System.out.println("\n You told me you want to go "+input);
			
			Room nextRoom = currentRoom.getExits(input);
			
					if (nextRoom != null) {
						currentRoom = nextRoom;
					}
		}
		scanner.close();
			
			/*
			will do soemthing with collect input()
			
			put a switch for the first index of the passed-in String[] should be the action
			the case will be what handles the target differently
		*/
	
		
		
		
		
		
		}
	/*
		private static void printRoom(Player player) {
			
		}
		
		private static String[] collectInput() {
			
		}
		
		private static void parse(String[] command, Player player) {
			
		}
		
		*/
	}
	
	
