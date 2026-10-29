package game;
import fixtures.Room;

public class RoomManager {
	
	Room startingRoom;
	
	public void init() {
		Room fence = new Room("Fence",
				 "a barrier enclosing the property. It is made of  metal.", 
				 "Long Description");
		Room frontyard = new Room("Frontyard", 
				"space in front of the house measured with a common unit of linear measure in English-speaking countries,", 
				"Long Description");
		Room driveway = new Room("Driveway", 
				"space in which to park vehicles, as at a place of business or a public event:", 
				"Long Description");
		Room basketballCourt = new Room("Basketball Court", 
				"Short an area open to the sky and mostly or entirely surrounded by buildings, walls, etc.\r\n"
				+ "", 
				"Long Description");
		Room garage = new Room("Garage", 
				"indoor area for parking or storing motor vehicles.", 
				"Long Description");
		Room basement = new Room("Basement", 
				"the lowermost portion of a structure", 
				"Long Description");
		Room laundryRoom = new Room("Laundry Room", 
				"a room or area, as in a home or apartment building, reserved for doing the family wash.", 
				"Long Description");
		Room hallway2 = new Room("hallway 2", 
				"Short Description", 
				"Long Description");
		Room porch = new Room("Porch", 
				"an exterior appendage to a building, forming a covered approach or vestibule to a doorway.\r\n"
				+ "", 
				"Long Description");
		Room livingRoom = new Room("Living Room", 
				"a room in a house for general and informal everyday use.", 
				"Long Description");
		Room hallway3 = new Room("hallway 3", 
				"Short Description", 
				"Long Description");
		Room funArea = new Room("Fun Area", 
				"Short Description", 
				"Long Description");
		Room gym = new Room("Gym", 
				"a building or room designed and equipped for indoor sports, exercise, or physical education.", 
				"Long Description");
		Room backyard = new Room("Backyard", 
				"space in back of the house measured with a common unit of linear measure in English-speaking countries,", 
				"Long Description");
		Room guestRoom = new Room("Guest Room", 
				": a bedroom for a person who is invited to visit or stay in someone's home", 
				"Long Description");
		Room guestRoomCloset = new Room("Guest Room Closet", 
				"Short Description", 
				"Long Description");
		Room guestRoomBathroom = new Room("Guest Room Bathroom", 
				"Short Description", 
				"Long Description");
		Room hallway1 = new Room("hallway 1", 
				"Short Description", 
				"Long Description");
		Room diningRoom = new Room("Dining Room", 
				"a room in a house or hotel in which meals are eaten.", 
				"Long Description");
		Room hallway7 = new Room("hallway 7", 
				"Short Description", 
				"Long Description");
		Room hallwaycloset = new Room("Hallway Closet", 
				"Short Description", 
				"Long Description");
		Room entertainmentStudio = new Room("Entertainment Studio", 
				"a room where an artist, photographer, sculptor, etc. works.", 
				"Long Description");
		Room hallway6 = new Room("hallway 6", 
				"Short Description", 
				"Long Description");
		Room kitchen = new Room("Kitchen", 
				"a room or place equipped for cooking.", 
				"Long Description");
		Room bathroomPublic = new Room("Public Bathroom", 
				"a room equipped for taking a bath or shower.", 
				"Long Description");
		Room hallway4 = new Room("hallway 4", 
				"Short Description", 
				"Long Description");
		Room hallway5 = new Room("hallway 5", 
				"Short Description", 
				"Long Description");
		Room gameRoom = new Room("Game Room", 
				"a room for relaxing or socializing in a house or public building", 
				"Long Description");
		Room hallway8 = new Room("hallway 8", 
				"Short Description", 
				"Long Description");
		Room masterBedroom = new Room("Master Bedroom", 
				"the main or largest bedroom in a house or apartment.", 
				"Long Description");
		Room masterBedroomHer = new Room("Master Bedroom Her Closet", 
				"Short Description", 
				"Long Description");
		Room masterBedroomHim = new Room("Master Bedroom His Closet", 
				"Short Description", 
				"Long Description");
		Room masterBedroomBathroom = new Room("Master Bedroom Bathroom", 
				"Short Description", 
				"Long Description");
		Room masterBedroomBathroomToilet = new Room("Master Bedroom Toilet", 
				"Short Description", 
				"Long Description");
		Room masterBedroomBathroomShower = new Room("Master Bedroom Shower", 
				"Short Description", 
				"Long Description");
		Room hallway9 = new Room("hallway 9", 
				"Short Description", 
				"Long Description");
		Room office = new Room("Office", 
				"a room, set of rooms, or building where the business of a commercial or industrial organization or of a professional person is conducted:", 
				"Long Description");
		Room attic = new Room("Attic", 
				"the part of a building, especially of a house, directly under a roof; garret.", 
				"Long Description");
		Room patio = new Room("Patio", 
				"an area, usually paved, adjoining a house and used as an area for outdoor lounging, dining, etc.", 
				"Long Description");
		Room backyardFun = new Room("Backyard Fun Area", 
				"Short Description", 
				"Long Description");
		Room grillArea = new Room("Grill Area", 
				"Short Description", 
				"Long Description");
		Room loungeArea = new Room("Outside Lounge Area", 
				"Short Description", 
				"Long Description");
		Room poolRoom = new Room("underwater Pool Room", 
				"Short Description", 
				"Long Description");
		Room pool = new Room("Pool", 
				"Short Description", 
				"Long Description");
		Room mote = new Room("mote", 
				"Short Description", 
				"Long Description");
		Room hallway10 = new Room("hallway 10", 
				"Short Description", 
				"Long Description");
		Room hallway11 = new Room("hallway 11", 
				"Short Description", 
				"Long Description");
		Room hallway12 = new Room("hallway 12", 
				"Short Description", 
				"Long Description");
		Room hallway13 = new Room("hallway 13", 
				"Short Description", 
				"Long Description");
		Room hallway14 = new Room("hallway 14", 
				"Short Description", 
				"Long Description");
		Room hallway15 = new Room("hallway 15", 
				"Short Description", 
				"Long Description");
		
		startingRoom = frontyard;
		
		fence.setExit(basketballCourt, gym, frontyard, backyard, null, mote);
		frontyard.setExit(driveway, funArea, porch, garage, null, null);
		driveway.setExit(basketballCourt, porch, garage, frontyard, null, null);
		basketballCourt.setExit(mote, hallway2, loungeArea, driveway, null, null);
		hallway2.setExit(basketballCourt, hallway4, hallway8, hallway1, null, null);
		garage.setExit(null, laundryRoom, null, driveway, null, basement);
		basement.setExit(null, null, null, null, garage, null);
		laundryRoom.setExit(garage, hallway1, hallway2, null, null, null);
		hallway1.setExit(null, null, null, null, null, null);
		porch.setExit(driveway, funArea, livingRoom, frontyard, null, null);  //put the direction you want to go in the brackets 
		livingRoom.setExit(hallway1, hallway3, hallway4, porch, null, null);
		hallway4.setExit(hallway2, hallway14, kitchen, livingRoom, null, null);
		funArea.setExit(porch, gym, null, frontyard, null, null);
		gym.setExit(hallway5, null, grillArea, funArea, null, mote);
		grillArea.setExit(backyardFun, fence, backyard, gym, null, mote);
		hallway5.setExit(hallway14, gym, hallway9, hallway3, null, null);
		guestRoom.setExit(hallway3, guestRoomCloset, bathroomPublic, null, null, null);
		guestRoomCloset.setExit(null, null, guestRoom, null, null, null);
		guestRoomBathroom.setExit(null, null, null, guestRoom, null, null);
		hallway3.setExit(livingRoom, guestRoom, hallway5, null, null, null);
		diningRoom.setExit(hallway6, null, null, null, null, null);
		hallway6.setExit(null,  diningRoom, hallway11, hallway4, null, null);
		entertainmentStudio.setExit(hallway7, null, null, null, null, basement);
		hallway7.setExit(kitchen, entertainmentStudio, hallway15, hallway14, null, null);
		hallwaycloset.setExit(null, null, null, hallway14, null, null);
		hallway14.setExit(hallway4, hallway5, null, hallwaycloset, null, null);
		kitchen.setExit(hallway8, hallway7, hallway10, hallway14, null, null);
		hallway8.setExit(bathroomPublic, kitchen, hallway9, hallway2, null, null);
		bathroomPublic.setExit(null, null, hallway8, null, null, null);
		hallway9.setExit(null, null, hallway11, hallway5, null, null);
		gameRoom.setExit(null, hallway12, null, null, null, null);
		hallway12.setExit(gameRoom, null, null, hallway9, null, null);
		masterBedroom.setExit(masterBedroomHer, masterBedroomHim, masterBedroomBathroom, hallway10, null, null);
		masterBedroomHer.setExit(null, null, null, null, null, null);
		masterBedroomHim.setExit(null, null, null, null, null, null);
		hallway10.setExit(hallway9, hallway15, masterBedroom, kitchen, null, null);
		masterBedroomBathroom.setExit(null, null, null, null, null, null);
		masterBedroomBathroomToilet.setExit(null, null, null, null, null, null);
		masterBedroomBathroomShower.setExit(null, null, null, null, null, null);
		hallway13.setExit(null, office, null, hallway11, null, null);
		office.setExit(hallway13, null, null, null, attic, null);
		hallway15.setExit(hallway10, hallway11, null, null, null, null);
		attic.setExit(null, null, null, null, null, office);
		hallway11.setExit(hallway15, null, hallway13, hallway5, null, null);
		patio.setExit(loungeArea, grillArea, backyardFun, hallway12, null, pool);
		loungeArea.setExit(basketballCourt, backyardFun, poolRoom, patio, null, null);
		poolRoom.setExit(null, null, null, null, patio, null);
		backyardFun.setExit(loungeArea, grillArea, fence, patio, null, pool);
		pool.setExit(loungeArea, grillArea, fence, backyardFun,  null, null);
		mote.setExit(gym, basketballCourt, pool, frontyard, backyardFun, null);
		backyard.setExit(fence, fence, fence, mote, fence, pool);
	}
	
	
	

}
