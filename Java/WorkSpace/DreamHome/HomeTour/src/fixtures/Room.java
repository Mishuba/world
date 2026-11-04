package fixtures;

public class Room extends Fixture {

	
	private Room[] exits = new Room[6];
	// 0 = left 1 = right 2 = down 3 = up 
	String direction;


public Room(String name, String shortDescription, String longDescription) {
	super(name, shortDescription, longDescription);
	this.exits = new Room[6]; // size is your choice
	
}

public void setExit(Room left, Room right, Room forward, Room backward, Room up, Room down) {
	
	exits[0] = left;
	exits[1] = right;
	exits[2] = forward;
	exits[3] = backward;
	exits[4] = up;
	exits[5] = down;
}


public Room getExits(String direction) {
	switch(direction) {
	case "left":
		return exits [0];
	
	case "right":
		return exits [1];
	
	case "forward":
		return exits [2];
	
	case "backwards": 
		return exits[3];
		
	case "up":
		return exits [4];
	
	case "down":
		return exits [5];
	
	default: return null;
	}

}



public String toString() {
	return "Room [name=" + name + "]";
}

}
