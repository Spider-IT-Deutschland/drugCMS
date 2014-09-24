<?php
# Start a new graphics environment.
# Params:
#   output width
#   output height
#   viewport left
#   viewport top
#   viewport right
#   viewport bottom
$ge = new GraphicsEnvironment(500, 500, -500, -500, 500, 500);

# Define colors to use
# Params:
#   name
#   red value
#   green value
#   blue value
#   alpha opacity value (0 = transparent, 1 = no transparency)
$ge->addColor('white', 255, 255, 255);
$ge->addColor('grey', 127, 127, 127);
$ge->addColor('black', 0, 0, 0);
$ge->addColor('semiblack', 0, 0, 0, 0.15);
$ge->addColor('red', 180, 0, 0);
$ge->addColor('semired', 180, 0, 0, 0.15);
$ge->addColor('green', 0, 180, 0);
$ge->addColor('semigreen', 0, 180, 0, 0.15);
$ge->addColor('blue', 0, 0, 180);
$ge->addColor('semiblue', 0, 0, 180, 0.15);

# Define fonts to use ('Arial' is predefined)
# Params:
#   name
#   path to the ttf file
#$ge->addFont('Arial', dirname(__FILE__) . '/arial.ttf');

# Start a group
# Params:
#   z-order of the group
$g0 = new GraphicsGroup(0);

# Draw some objects into the group
# Params:
#   z-order within the group
#   color name
#   position (array of left, top, right, bottom)
$g0->add(new GraphicsFilledRectangle(0, 'semiblack', array(-500, -500, 500, 500)));
$g0->add(new GraphicsFilledOval(1, 'white', array(-400, -400, 400, 400)));
$g0->add(new GraphicsOval(2, 'grey', array(-400, -400, 400, 400)));
$g0->add(new GraphicsOval(2, 'grey', array(-300, -300, 300, 300)));
$g0->add(new GraphicsOval(2, 'grey', array(-200, -200, 200, 200)));
$g0->add(new GraphicsOval(2, 'grey', array(-100, -100, 100, 100)));
$g0->add(new GraphicsLine(5, 'grey', array(-415, 0, 415, 0)));
$g0->add(new GraphicsLine(5, 'grey', array(0, -415, 0, 415)));
$g0->add(new GraphicsLine(5, 'grey', array(-295, -295, 295, 295)));
$g0->add(new GraphicsLine(5, 'grey', array(295, -295, -295, 295)));

# Draw simple text into the group
# Params:
#   z-order within the group
#   color name
#   text to draw
#   position (array of x and y coordinates)
#   angle to rotate the text by, 0 is default
#   horizontal position (left / center / right), 'center' is default
#   vertical position (top / center / bottom), 'center' is default
#$g0->add(new GraphicsText(5, 'grey', 'Test1', array(0, -450), 0, 'center', 'center'));
#$g0->add(new GraphicsText(5, 'grey', 'Test2', array(320, -320), 315, 'center', 'center'));
#$g0->add(new GraphicsText(5, 'grey', 'Test3', array(450, 0), 270, 'center', 'center'));
#$g0->add(new GraphicsText(5, 'grey', 'Test4', array(320, 320), 45, 'center', 'center'));
#$g0->add(new GraphicsText(5, 'grey', 'Test5', array(0, 450), 0, 'center', 'center'));
#$g0->add(new GraphicsText(5, 'grey', 'Test6', array(-320, 320), 315, 'center', 'center'));
#$g0->add(new GraphicsText(5, 'grey', 'Test7', array(-450, 0), 90, 'center', 'center'));
#$g0->add(new GraphicsText(5, 'grey', 'Test8', array(-320, -320), 45, 'center', 'center'));

# Draw text into the group using a TrueType font file
# Params:
#   z-order within the group
#   color name
#   text to draw
#   position (array of x and y coordinates)
#   angle to rotate the text by, 0 is default
#   name of the font to use ('Arial' is predefined), 'Arial' is default
#   size to draw the text in, 20 is default
#   horizontal position (left / center / right), 'center' is default
#   vertical position (top / center / bottom), 'center' is default
$g0->add(new GraphicsTextTTF(5, 'grey', 'Test 1', array(0, -450), 0, 'Arial', 20, 'center', 'center'));
$g0->add(new GraphicsTextTTF(5, 'grey', 'Test 2', array(320, -320), 315, 'Arial', 20, 'center', 'center'));
$g0->add(new GraphicsTextTTF(5, 'grey', 'Test 3', array(450, 0), 270, 'Arial', 20, 'center', 'center'));
$g0->add(new GraphicsTextTTF(5, 'grey', 'Test 4', array(320, 320), 45, 'Arial', 20, 'center', 'center'));
$g0->add(new GraphicsTextTTF(5, 'grey', 'Test 5', array(0, 450), 0, 'Arial', 20, 'center', 'center'));
$g0->add(new GraphicsTextTTF(5, 'grey', 'Test 6', array(-320, 320), 315, 'Arial', 20, 'center', 'center'));
$g0->add(new GraphicsTextTTF(5, 'grey', 'Test 7', array(-450, 0), 90, 'Arial', 20, 'center', 'center'));
$g0->add(new GraphicsTextTTF(5, 'grey', 'Test 8', array(-320, -320), 45, 'Arial', 20, 'center', 'center'));

# Add the group to the graphics environment
$ge->add($g0);

# Start a second group
# Params:
#   z-order of the group
$g1 = new GraphicsGroup(1);

# Draw some polygon objects into the group
# Params:
#   z-order within the group
#   color name
#   position (array of x and y coordinates of each point)
$g1->add(new GraphicsFilledPolygon(100, 'semigreen', array(
                                                            -150, 0,
                                                            -125, -125,
                                                            0, -190,
                                                            200, -200,
                                                            300, 0,
                                                            250, 250,
                                                            0, 250,
                                                            -135, 135
                                                        )));
$g1->add(new GraphicsPolygon(100, 'green', array(
                                                            -150, 0,
                                                            -125, -125,
                                                            0, -190,
                                                            200, -200,
                                                            300, 0,
                                                            250, 250,
                                                            0, 250,
                                                            -135, 135
                                                        )));

# Add the second group to the graphics environment
$ge->add($g1);

# Render the graphics environment
# Params:
#   (none)
$ge->render();

# Write the output image to a file
# Params:
#   path and filename to write to
$ge->saveAsPng('test.png');

# or

# Get the output image as image object
# Params:
#   (none)
#$ge->getImage();

# Destroy the graphics environment (free's the memory destroying the classes and the image)
unset($ge);
?>