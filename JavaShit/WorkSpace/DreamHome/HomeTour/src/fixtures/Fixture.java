package fixtures;

public abstract class Fixture {
	
	String name = "name of fixture";
	String shortDescription = " this a the blank blank blank";
	String longDescription = " this should be something very descriptive of whatever the ffixture is you feel me";
	
	public Fixture() {
	}
	
	public Fixture(String name, String shortDescription, String longDescription) {
		this.name = name;
		this.shortDescription = shortDescription;
		this.longDescription = longDescription;
		
	}
	
	public String getName() {
		return name;
	}
	
	public void setName(String name) {
		this.name = name;
	}
	
	public String getShortDescription() {
		return shortDescription;
		
	}
	
	public void setShortDescription(String shortDescription) {
		this.shortDescription = shortDescription;
	}
	
	public String getLongDescription() {
		return longDescription;
	}
	
	public void setLongDescription(String longDescription) {
		this.longDescription = longDescription;
	}

}
/*
 * 	washing machine
 * 	dryer
 * 
 * 	Sink
 * 	Stove
 * 	Fridge
 * 	Oven
 * 	Microwave
 * 	Cabinets
 * 	Storage closet
 * Masterbedroom closet
 * Guestroom Closet
 * Hallway Closet
 * guest bathroom
 * master bathroom
 * Backyard
 * Garage
 * Attic
 * 	airducts
 * Basement	
 * 	water heater
 */