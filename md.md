                                                         NATIONAL INSTITUTE OF TRANSPORT

                        DEPARTMENT OF COMPUTING AND COMMUNICATION TECHNOLOGY

A WEB BASED E-COMMERCE SYSTEM WITH AI BASED PERSONALIZED RECOMMENDATION


                                                                                BY
                                                              HIDAYA NURU HUSSEIN
                                                                    NIT/BIT/2023/2155

                                BACHELOR DEGREE IN INFORMATION TECHNOLOGY

                                                 SUPERVISOR: MR. MIKE M. KAKWAYA
                                                             ACADEMIC YEAR: 2025/2026





	



TABLE OF CONTENT
Contents
CHAPTER ONE: INTRODUCTION	4
BACKGROUND	4
PROBLEM STATEMENT	4
OBJECTIVES	4
SIGNIFICANCE OF THE PROJECT	5
SCOPE OF THE PROJECT	5
CHAPTR TWO: LITERATURE REVIEW	6
1. Introduction.	6
2. Existing Work	6
3. Gap Identification Existing studies	6
4. Relation to the Proposed system.	6
CHAPTER THREE: SYSTEM DESIGN	7
Introduction	7
1. System Architecture Diagram (AI-Based E-Commerce)	7
2. Use Case Diagram	9
3. Flowchart	11
REFERENCES	18












CHAPTER ONE: INTRODUCTION
BACKGROUND
The rapid growth of e-commerce has transformed how businesses operate and how customers purchase products. In recent years, clothing retailers have increasingly adopted online platforms to improve accessibility, convenience, and customer reach. However, many of these platforms lack intelligent systems that can personalize the shopping experience for individual users.
Artificial Intelligence (AI) has emerged as a powerful tool in modern digital systems. It enables the analysis of user behavior, preferences, and purchasing patterns to deliver customized services. In e-commerce, AI-based recommendation systems help customers discover products that match their interests, thereby improving user satisfaction and engagement.
Therefore, integrating AI into a web-based clothing e-commerce system can significantly enhance customer experience and business performance by providing personalized product recommendations.

PROBLEM STATEMENT
Despite the widespread adoption of e-commerce platforms, many existing systems do not provide real-time personalized product recommendations tailored to individual users. Most platforms display general or popular products without considering user preferences, behavior, or purchase history.
This limitation reduces user engagement, affects customer satisfaction, and may lead to lower sales. Therefore, there is a need to develop an intelligent e-commerce system that can analyze user data and provide personalized product recommendations in real time.

OBJECTIVES
Main Objective:
•	To design and implement a web-based clothing e-commerce system integrated with AI for personalized product recommendations.
Specific Objectives:
•	To apply association rule mining algorithms (Apriori) to analyze customer purchasing behavior.
•	To design and develop an AI-based recommendation system that provides real-time personalized product suggestions.
•	To design and implement a structured database for storing user, product, and transaction data.
•	To develop a web-based e-commerce platform for selling clothing products.




SIGNIFICANCE OF THE PROJECT
•	This project is important both practically and academically.
•	Practically, the system will improve user experience by providing personalized product recommendations based on individual behavior. It will also help businesses increase sales by suggesting products that customers are more likely to purchase, thereby enhancing customer satisfaction and loyalty.

•	Academically, the project contributes to the field of information technology by demonstrating the application of AI in e-commerce systems. It also provides a foundation for future research in personalized recommendation systems.

SCOPE OF THE PROJECT
	This project focuses on the development of a web-based clothing e-commerce system with two main users: Admin and Customer.
	The system will include features such as product management, user registration, shopping cart, checkout, and simulated payment processing. AI-based recommendations will be generated using transactional data through a Python-based recommendation engine integrated via a Flask API.
However, the project will not include advanced features such as real payment gateway integration, mobile applications, or large-scale deployment.

		








CHAPTR TWO: LITERATURE REVIEW

 1. Introduction
E-commerce has grown rapidly, transforming how consumers shop and interact with brands. Clothing e-commerce faces unique challenges due to diverse product ranges and subjective fashion preferences. To enhance user experience and increase sales, many platforms are adopting artificial intelligence (AI) for personalized recommendations.
 2. Existing Work
•	Miklosik (2019) explored machine learning tools in digital marketing to predict user preferences by analyzing data such as browsing patterns and purchase history. This study demonstrates AI’s potential to provide personalized recommendations for online clothing platforms.
•	Campbell (2020) examined how AI can turn raw data into actionable insights for marketers. By identifying user preferences, AI-driven recommendations improve customer engagement and create a more tailored.
•	 Kaplan (2017) highlighted the risks of relying solely on AI, stressing the importance of human oversight. This ensures accuracy, reliability, and trust, which is particularly important in clothing e-commerce where consumer preferences are subjective.

3. Gap Identification
Existing studies do not consider regional differences in consumer behaviour. Fashion preferences and shopping habits vary by location, making generic AI recommendation systems less relevant for specific markets.

4. Relation to the Proposed System
This project addresses the geographical gap by developing a web-based clothing e-commerce system with AI-based personalized recommendations tailored to users’ regional preferences. Human oversight will complement AI to ensure recommendations are accurate, relevant, and trustworthy. 
CHAPTER THREE: SYSTEM DESIGN
Introduction
System design consist of all design which made by using analysing the consist use case, Flow chart and system architecture.
 1. System Architecture Diagram (AI-Based E-Commerce)
Purpose:
Shows how the main components of the system interact: frontend, backend, AI engine, and database.
Components to Include:
User Interface (Frontend): Web application where users interact with the system.
Application Server (Backend): Handles business logic, APIs, and communication between frontend, database, and recommendation engine.
Recommendation Engine: AI component using Apriori or FP-Growth to generate personalized product suggestions.
Database: Stores all data (users, products, transactions).
Output: Personalized recommendations displayed to the user.

	












 
2. Use Case Diagram
Purpose:
Shows the interaction between users (actors) and the system (use cases).
Actors:
Customer: Browses products, purchases items, views recommendations.
Admin: Manages products, users, orders, and updates AI model.
Customer Use Cases:
Register / Login
Browse Products
Add to Cart
Make Purchase
View Order History
View Recommendations	

Admin Use Cases:
Login
Manage Products
Manage Users
View Reports
Monitor Transactions
Update AI Model
Simple Layout (Text version):



Use case diagram
























3. Flowchart
Purpose:
Shows the sequence of activities from user actions to AI recommendations and order completion Yes → Enter Shipping & Payment
If No → Go back to Browse
Payment Successful? (Decision)
If Yes → Store Transaction → Generate Recommendations → Display to User → End
If No → Prompt for Retry → Payment.

  
Flowchart diagram 


REFERENCE

Miklosik,“Towards theadoptionofmachinelearning-basedanalyticaltools in digital marketing,” IEEE Access, 2019.

C.S.Campbell,“From datatoaction:Howmarketerscanleverage AI,” Business Horizons, pp. 227–243, 2020.

J. Kaplan,“ArtificialIntelligence:ThinkAgain,” Communications ofthe ACM 60, pp. 36–38, 2017. 


