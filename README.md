# JxJ : Joomla! "multiplies" Joomla!

## System - JxJ : plg_system_jxj

TLDR; JXJ is system plugin to make 2 Joomla! websites communicate via webservices.
Whether it is:

```

j3x -> j4x
j3x <- j4x
j3x -> j3x
j4x -> j4x

```

### EDIT: Friday, July 14th 2023

As j3x is near EOL(End Of Life) as of August 2023. This plugin now focuses on j4x (Joomla 4.x) and j5x (Joomla 5.x)

```

j4x -> j4x
j4x -> j5x
j5x -> j5x
j5x -> j4x

```

I called it JxJ pronounced J "x" J because it's Joomla! multiplies Joomla!, Joomla! website communicating with another
Joomla! website. That's it. Kinda like a CROSS JOIN for geeky database folks and girls.

## WHY?

My name is Alexandre J-S William ELISÉ, I am a French web developer and Joomla! specialist. The idea came from a demo I
needed to make for Virtual Joomladay France on Friday, June 12th 2020. A friend of mine, Marc DECHÈVRE, told me that it
would be great to make two Joomla! websites communicate together.

## WHAT?

It is a unified system plugin that should work on both Joomla! 4.x and Joomla! 5.x

## HOW?

j3x -> j4x communication is using PHP streams to make an HTTP request to the j4x webservice endpoint providing the api
token.
For j4x -> j3x communication it is more tricky because there are no webservices by default in Joomla! 3. That's why I
chose to use the com_api provided by Techjoomla for this purpose. If you would like to use another solution it is up to
you to change the code accordingly but I chose com_api for convenience, ease of use and efficiency. Feel free to use
what suits your use case the best.

### EDIT: Friday, July 14th 2023

> Now this plugin shift focus to make communication between j4x -> j5x and j4x <- j5x since j3x is close to EOL (End Of
> Life) which should be in August 2023

# INSTRUCTIONS

- Get the latest build in build directory or build it yourself by typing "make all" in your terminal after cloning this
  repository.
- Install the plugin on your website where the request comes from. For j3x --> j4x the request comes from j3x website so
  you install the plugin on your j3x website.
- Configure the plugin pay a special attention to base path and api token information. It's the information of the
  website where the request goes to. In the example above it's your j4x website. The category id is the category on the
  j4x website where you want to create articles. For now creating articles is the only action supported.
- On your j3x website go to your admin menu create an article and save it.
  onContentAfterSave the plugin will communicate with your j4x website using the secure token and create the same
  article there.
  -That's roughly how it works. Feel free to give your feedback and share your improvements.

## SPECIAL THANKS

I would like to say thanks to Marc DECHÈVRE for giving me the opportunity to share my knowledge of Joomla! publicly in a
live YouTube session on Friday, June 12th 2020. It's in French, but hopefully you will get the overall idea of what are
webservices in Joomla! 4.

## THANKS

- Techjoomla team for their cool extension com_api
- Joomla!
- Joomla! community for being so awesome

## CONTRIBUTORS

- Anyone is more than welcome to contribute to plg_system_jxj to make it even better.
  Take care. And have a delightful day. Super Joomlers!

--------------------------------------------

## INFOS

>
English: [Click here to get in touch](https://github.com/mralexandrelise/mralexandrelise/blob/master/community.md "Get in touch")

>
Français: [Cliquez ici pour me contacter](https://github.com/mralexandrelise/mralexandrelise/blob/master/community.md "Me contacter")
