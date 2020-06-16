set terminal png size 500,500
set output '6gnuplot.png'
set title 'Gnuplot Graphic'
plot[0:2][0:3] x*x*sin(x)
