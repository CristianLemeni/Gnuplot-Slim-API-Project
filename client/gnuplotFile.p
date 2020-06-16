set terminal png size 500,500
set output 'D:/gnuplot/client/6gnuplot.png'
set title 'Gnuplot Graphic'
plot[-1:2][0:3] sin(x)
