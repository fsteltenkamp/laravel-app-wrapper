#!/usr/bin/perl
# Rewrites the old fltc `theme` props to the role-based `color` prop (fltc 56f4129+).
#
#   perl scripts/fltc-theme-to-color.pl $(grep -rl theme app/src/resources/views)
#
# Solid buttons with :theme="$mainTheme" become color="primary", theme="red" becomes
# color="danger", and every other :theme="$mainTheme" / :theme="$accentTheme" /
# theme="zinc" is dropped so the component uses the theme's `base` role. Review the diff.
use strict; use warnings;
local $/;
for my $file (@ARGV) {
    open my $fh, '<', $file or die; my $src = <$fh>; close $fh;
    my $orig = $src;
    $src =~ s{(<x-fltc::([\w.-]+)\b(?:"[^"]*"|[^">])*>)}{fix($1, $2)}ge;
    if ($src ne $orig) { open my $out, '>', $file or die; print $out $src; close $out; print "updated $file\n"; }
}
sub drop { my ($t, $attr) = @_; $t =~ s/\n[ \t]*\Q$attr\E[ \t]*(?=\n)//g; $t =~ s/[ \t]+\Q$attr\E//g; return $t; }
sub fix {
    my ($tag, $name) = @_;
    my $isButton = $name eq 'button' || $name eq 'button.link';
    my $outline = $tag =~ /variant="outline"/;
    $tag =~ s/\btheme="red"/color="danger"/g;
    $tag = drop($tag, 'theme="zinc"');
    $tag = drop($tag, ':theme="$accentTheme"');
    if ($isButton && !$outline) { $tag =~ s/:theme="\$mainTheme"/color="primary"/g; }
    else { $tag = drop($tag, ':theme="$mainTheme"'); }
    return $tag;
}
